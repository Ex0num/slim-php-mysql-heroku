<?php

use Firebase\JWT\JWT;
require_once './models/Usuario.php';
use \App\Models\Usuario as Usuario;

class AutentificadorJWT
{
    private static $claveSecreta = '123TokenTesting321123';
    private static $tipoEncriptacion = ['HS256'];

    public static function CrearToken($datosRecibidos)
    {
        $ahora = time();

        //Duracion de token de 60 minutos
        $payload = array(
            'iat' => $ahora, //Cuando fue creado
            'exp' => $ahora + (6000000), //Cuando expira
            'aud' => self::Aud(), //Identifica a los receptores del JWT (audiencia)
            'data' => $datosRecibidos, //La data que movemos
            'app' => "Token de login"
        );

        //Aca se hace el encode del JWT con nuestra firma/clave-secreta definida
        return JWT::encode($payload, self::$claveSecreta);
    }

    public static function VerificarToken($token)
    {
        //Se fija que no este vacio
        if (empty($token) == true) 
        {
            throw new Exception("El token esta vacio.");
        }

        try 
        {
            //Se intenta decodificar el token (tanto la clave como el tipo de incriptacion)
            $decodificado = JWT::decode(
                $token,
                self::$claveSecreta,
                self::$tipoEncriptacion
            );
        } 
        catch (Exception $e) 
        {
            throw $e;
        }

        //Si la "audencia" del token decodificado no es la misma que la audiencia definida en nuestra clase
        if ($decodificado->aud !== self::Aud()) 
        {
            throw new Exception("El Token fue manipulado.");
        }
    }

    public static function ObtenerPayLoad($token)
    {

        //Si el token esta vacio, aviso.
        if (empty($token) == true) 
        {
            throw new Exception("El token esta vacio.");
        }

        //Aca se hace el encode del JWT con nuestra firma/clave-secreta definida
        return JWT::decode($token, self::$claveSecreta, self::$tipoEncriptacion);
    }

    public static function ObtenerData($token)
    {
        //Si el token esta vacio, aviso.
        if (empty($token) == true) 
        {
            throw new Exception("El token esta vacio.");
        }

        //Devuelvo el decode del token, ESPECIFICAMENTE LA DATA.
        return JWT::decode($token, self::$claveSecreta, self::$tipoEncriptacion)->data;
    }

    private static function Aud()
    {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }

    public static function DevolverIdUserResponsable($request)
    {
        //Recibimos el token por "Authorization".
        $header = $request->getHeaderLine('Authorization');

        if ($header != null)
        {   
            //Le saco "Bearer"
            $token = trim(explode("Bearer", $header)[1]);

            //Llamo a mi funcion que INTENTA Obtener la data del token. Solo la data. Luego Me decodifico lo devuelto
            $dataToken = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
            $dataUser = json_decode($dataToken);
            
            //De la data decodificada (User y clave) me voy a guardar el user y buscarlo en la db.
            $nombreUsuarioResponsable = $dataUser->datos->user;

            //Me traigo todos los usuarios que coincidan con ese nombre (solo puede ser uno). Y me guardo su ID.
            $usuario = new App\Models\Usuario();
            $arrayUsuarios = $usuario->where('user',$nombreUsuarioResponsable)->get();

            if ($arrayUsuarios != null)
            {
                $idUsuarioResponsable = $arrayUsuarios[0]->id;
                return $idUsuarioResponsable;
            }
        }
  
        return null;
    }

    public static function DevolverTipoUserResponsable($request)
    {
        //Recibimos el token por "Authorization".
        $header = $request->getHeaderLine('Authorization');

        if ($header != null)
        {
            //Le saco "Bearer"
            $token = trim(explode("Bearer", $header)[1]);

            //Llamo a mi funcion que INTENTA Obtener la data del token. Solo la data. Luego Me decodifico lo devuelto
            $dataToken = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
            $dataUser = json_decode($dataToken);
            
            //De la data decodificada (User y clave) me voy a guardar el user y buscarlo en la db.
            $nombreUsuarioResponsable = $dataUser->datos->user;

            //Me traigo todos los usuarios que coincidan con ese nombre (solo puede ser uno). Y me guardo su ID.
            $usuario = new App\Models\Usuario();
            $arrayUsuarios = $usuario->where('user',$nombreUsuarioResponsable)->get();

            if ($arrayUsuarios != null)
            {
                $tipoUsuarioResponsable = $arrayUsuarios[0]->tipo;
            }

            return $tipoUsuarioResponsable;
        }   

        return null;
    }

    public static function DevolverEstadoUserResponsable($request)
    {
        //Recibimos el token por "Authorization".
        $header = $request->getHeaderLine('Authorization');

        if ($header != null)
        {
            //Le saco "Bearer"
            $token = trim(explode("Bearer", $header)[1]);

            //Llamo a mi funcion que INTENTA Obtener la data del token. Solo la data. Luego Me decodifico lo devuelto
            $dataToken = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
            $dataUser = json_decode($dataToken);
            
            //De la data decodificada (User y clave) me voy a guardar el user y buscarlo en la db.
            $nombreUsuarioResponsable = $dataUser->datos->user;

            //Me traigo todos los usuarios que coincidan con ese nombre (solo puede ser uno). Y me guardo su ID.
            $usuario = new App\Models\Usuario();
            $arrayUsuarios = $usuario->where('user',$nombreUsuarioResponsable)->get();

            if ($arrayUsuarios != null)
            {
                $estadoUsuarioResponsable = $arrayUsuarios[0]->estado;
            }

            return $estadoUsuarioResponsable;
        }   

        return null;
    }

    public static function VerificarUsuario($token)
    {
        $existeUsuario = false;

        //Llamo a mi funcion que INTENTA Obtener la data del token. Solo la data. Luego Me decodifico lo devuelto
        $dataToken = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
        $dataUser = json_decode($dataToken);

        //De la data decodificada (User y clave) me voy a guardar el user y buscarlo en la db.
        $nombreUsuarioResponsable = $dataUser->datos->user;
        $claveUsuarioResponsable = $dataUser->datos->clave;

        if ($nombreUsuarioResponsable != null && $claveUsuarioResponsable != null)
        {
            $usr = new \App\Models\Usuario();
            $usuariosEncontrados = $usr->where('user', '=', $nombreUsuarioResponsable)->get();

            if ($usuariosEncontrados != null)
            {
                $contador = 0;

                foreach($usuariosEncontrados as $usuario)
                {
                    $result = password_verify($claveUsuarioResponsable,$usuario->clave);
                
                    if ($result == true)
                    {
                        $existeUsuario = true;
                        return $existeUsuario;
                    }

                    $contador++;
                }
                
            }
        }

        return $existeUsuario;
    }

}