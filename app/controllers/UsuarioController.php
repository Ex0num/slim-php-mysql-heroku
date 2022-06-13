<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Usuario as Usuario;

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
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $idRecibida = $args['id'];

        //Busco la ID recibida y me guardo al usuario encontrado si es que se encontró alguno.
        $usuarioEncontrado = App\Models\Usuario::find($idRecibida);
            
        if ($usuarioEncontrado != null)
        {
            //El ORM borra automaticamente el usuario en la DB.
            $usuarioEncontrado->delete();
            $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Usuario no eliminado")); 
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $id = $args['id'];

        //Busco en la DB en el campo 'id' el valor del ID recibido y me guardo al usuario encontrado si es que se encontró alguno.
        $usuarioEncontrado = App\Models\Usuario::where('id', '=', $id)->first();
        
        //Leo el json "RAW" de Postman y hago el decode de los datos nuevos para modificar al usuario .
        $body = json_decode(file_get_contents("php://input"), true);

        if ($usuarioEncontrado != null)
        {
            $userRecibido = $body['user'];
            $claveRecibida = $body['clave'];
            $nombreRecibido = $body['nombre'];
            $apellidoRecibido = $body['apellido'];
            $edadRecibida = $body['edad'];
            $estadoRecibido = $body['estado'];
            $tipoRecibido = $body['tipo'];

            //Piso los datos 'viejos' por los 'nuevos' datos del usuario a modificar.
            $usuarioEncontrado->user = $userRecibido;
            $usuarioEncontrado->clave = $claveRecibida;
            $usuarioEncontrado->nombre = $nombreRecibido;
            $usuarioEncontrado->apellido = $apellidoRecibido;
            $usuarioEncontrado->edad = $edadRecibida;
            $usuarioEncontrado->estado = $estadoRecibido;
            $usuarioEncontrado->tipo = $tipoRecibido;
        
            //El ORM guarda automaticamente el usuario en la DB.
            $usuarioEncontrado->save();
            $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Usuario no modificado")); 
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');    
    }

    public function TraerTodos($request, $response, $args)
    {
        //Me traigo a todos los usuarios.
        $listaUsuarios = App\Models\Usuario::all();
        
        //Voy a devolver como texto dentro de la respuesta a los usuarios encontrados.
        $payload = json_encode(array("listaUsuarios" => $listaUsuarios));

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $idRecibido = $args['id'];

        //Me traigo a todos los usuarios y busco al usuario con el ID recibido.
        $listaUsuarios = Usuario::all();
        $usuarioEncontrado = $listaUsuarios->find($idRecibido);

        //Voy a devolver como texto dentro de la respuesta al usuario encontrado.
        $payload = json_encode($usuarioEncontrado);

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>
