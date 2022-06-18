<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Usuario as Usuario;
date_default_timezone_set("America/Buenos_Aires");

class UsuarioController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        //Recibo el body del form-data en forma de array asociativo.
        $parametros = $request->getParsedBody();

        $userRecibido = $parametros['user'];
        $claveRecibida = $parametros['clave'];
        $nombreRecibido = $parametros['nombre'];
        $apellidoRecibido = $parametros['apellido'];
        $edadRecibida = $parametros['edad'];
        $estadoRecibido = $parametros['estado'];
        $tipoRecibido = $parametros['tipo'];

        //Antes de dar de alta el usuario valido que no exista alguien con el mismo nombre de usuario.
        $estaRepetido = $this->esUsuarioRepetido($userRecibido);

        if ($estaRepetido == false)
        {
            // Creo el usuario.
            $usuarioCreado = new Usuario();

            //Hasheo la pass y asigno los datos recibidos.
            $claveHasheada = password_hash($claveRecibida, PASSWORD_DEFAULT);

            $usuarioCreado->user = $userRecibido;
            $usuarioCreado->clave = $claveHasheada;
            $usuarioCreado->nombre = $nombreRecibido;
            $usuarioCreado->apellido = $apellidoRecibido;
            $usuarioCreado->edad = $edadRecibida;
            $usuarioCreado->estado = $estadoRecibido;
            $usuarioCreado->tipo = $tipoRecibido;
            
            //El ORM guarda automaticamente el usuario en la DB.
            $usuarioCreado->save();

            //Retorno la respuesta con el body que contiene un mensaje.
            $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
        }
        else
        {
            //Retorno la respuesta con el body que contiene un mensaje.
            $payload = json_encode(array("mensaje" => "El Usuario no pudo ser creado. Ya existe uno con ese nombre de usuario."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//
        if ($tipoUsuarioResponsable == "socio")
        {
            //Recibo la ID por el "link".
            $idRecibida = $args['id'];

            //Busco la ID recibida y me guardo al usuario encontrado si es que se encontró alguno.
            $usuarioEncontrado = App\Models\Usuario::find($idRecibida);
                
            if ($usuarioEncontrado != null)
            {
                //El ORM borra automaticamente el usuario en la DB.
                $usuarioEncontrado->delete();
                $payload = json_encode(array("mensajeFinal" => "Usuario borrada con exito.",
                "exito" => "exitoso","tipo" => "baja","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => $usuarioEncontrado->id,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "Usuario borrado sin exito. No se encontró el usuario.",
                "exito" => "fallido","tipo" => "baja","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Usuario borrada sin exito. No posee permisos.",
            "exito" => "fallido","tipo" => "baja","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        //----------------------------------------------------------------------------------//
 
        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio y mozo (solo a estado = cerrada)
        //----------------------------------------------------------------------------------//

        if ($tipoUsuarioResponsable == "socio" || $tipoUsuarioResponsable == "mozo")
        {
            
            //Recibo la ID por el "link".
            $id = $args['id'];

            //Busco en la DB en el campo 'id' el valor del ID recibido y me guardo al usuario encontrado si es que se encontró alguno.
            $usuarioEncontrado = App\Models\Usuario::where('id', '=', $id)->first();
            
            //Leo el json "RAW" de Postman y hago el decode de los datos nuevos para modificar al usuario .
            $body = json_decode(file_get_contents("php://input"), true);

            if ($usuarioEncontrado != null)
            {
                $nombreRecibido = $body['nombre'];
                $apellidoRecibido = $body['apellido'];
                $edadRecibida = $body['edad'];
                $estadoRecibido = $body['estado'];
                $tipoRecibido = $body['tipo'];

                //Piso los datos 'viejos' por los 'nuevos' datos del usuario a modificar.
                $usuarioEncontrado->nombre = $nombreRecibido;
                $usuarioEncontrado->apellido = $apellidoRecibido;
                $usuarioEncontrado->edad = $edadRecibida;
                $usuarioEncontrado->estado = $estadoRecibido;
                $usuarioEncontrado->tipo = $tipoRecibido;
            
                //El ORM guarda automaticamente el usuario en la DB.
                $usuarioEncontrado->save();

                $payload = json_encode(array("mensajeFinal" => "Usuario modificado con exito.",
                "exito" => "exitoso","tipo" => "modificacion","hora" => date('h:i:s'),
                "idUsuarioResponsable" => $idUsuarioResponsable, "idUsuario" => $usuarioEncontrado->id,
                "idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "Usuario modificada sin exito. No se encontro el usuario a modificar.",
                "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Usuario modificado sin exito. No posee permisos.",
            "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');    
    }

    public function TraerTodos($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio y mozo
        //----------------------------------------------------------------------------------//
        if ($tipoUsuarioResponsable == "socio" || $tipoUsuarioResponsable == "mozo")
        {
            //Me traigo a todos los usuarios.
            $listaUsuarios = App\Models\Usuario::all();
            
            //Voy a devolver como texto dentro de la respuesta a los usuarios encontrados.
            $JsonUsuarios = json_encode(array("listaUsuarios" => $listaUsuarios));

            //Imprimo todas los usuarios.
            echo($JsonUsuarios);

            $payload = json_encode(array("mensajeFinal" => "Se listaron todas los usuarios.",
            "exito" => "exitoso","tipo" => "listarTodos","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "El listado de todas los usuarios no fue posible. No posee permisos.",
            "exito" => "fallido","tipo" => "listarTodos","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio y mozo
        //----------------------------------------------------------------------------------//
        if ($tipoUsuarioResponsable == "socio" || $tipoUsuarioResponsable == "mozo")
        {
            //Recibo la ID por el "link".
            $idRecibido = $args['id'];

            //Me traigo a todos los usuarios y busco al usuario con el ID recibido.
            $listaUsuarios = Usuario::all();
            $usuarioEncontrado = $listaUsuarios->find($idRecibido);

            //Voy a devolver como texto dentro de la respuesta al usuario encontrado.
            $JsonUsuarios = json_encode($usuarioEncontrado);

            //Imprimo el usuario.
            echo($JsonUsuarios);

            $payload = json_encode(array("mensajeFinal" => "El listar de un usuario fue realizado.",
            "exito" => "exitoso","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => $usuarioEncontrado->id, "idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "El listar de un usuario no fue posible. No posee permisos.",
            "exito" => "fallido","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function esUsuarioRepetido($nombreUsuarioRecibido)
    {
        $estaRepetido = false;

        if ($nombreUsuarioRecibido != null)
        {
            //Busco en la DB si se repite el nombre de usuario recibido.
            $usr = new \App\Models\Usuario();
            
            //Me traigo todo aquellos usuarios que coincidan con el nombre recibido
            $usuariosEncontrados = $usr->where('user',$nombreUsuarioRecibido)->get();
            
            //Si encontre alguno que coincida y (en consecuencia, no es nulo el resultado devuelto), esta repetido
            if (count($usuariosEncontrados) > 0)
            {
                $estaRepetido = true;
            }
        }
    
        return $estaRepetido;
    }
}
?>
