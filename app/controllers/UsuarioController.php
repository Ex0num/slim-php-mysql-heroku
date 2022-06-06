<?php
require_once './models/Usuario.php';
//require_once './interfaces/IApiUsable.php';

class UsuarioController extends Usuario //implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $user = $parametros['user'];
        $clave = $parametros['clave'];
        $nombre = $parametros['nombre'];
        $apellido = $parametros['apellido'];
        $edad = $parametros['edad'];
        $estado = $parametros['estado'];
        $tipo = $parametros['tipo'];

        // Creamos el usuario
        $usr = new Usuario();
        $usr->user = $user;
        $usr->clave = $clave;
        $usr->nombre = $nombre;
        $usr->apellido = $apellido;
        $usr->edad = $edad;
        $usr->estado = $estado;
        $usr->tipo = $tipo;
        $usr->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /*
    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre
        $usr = $args['usuario'];
        $usuario = Usuario::obtenerUsuario($usr);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    */

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuarios" => $lista));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /*
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        Usuario::modificarUsuario($nombre);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuarioId = $parametros['usuarioId'];
        Usuario::borrarUsuario($usuarioId);

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    */
}
