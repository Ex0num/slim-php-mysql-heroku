<?php
require_once './models/Mesa.php';
//require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa //implements IApiUsable
{

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $numeroMesaRecibido = $parametros['numero'];
        $estadoRecibido = $parametros['estado'];

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $codigoAlfanumerico = substr(str_shuffle($permitted_chars), 0, 10);

        // Creamos la nueva mesa
        $mesa = new Mesa();
        $mesa->numero = $numeroMesaRecibido;
        $mesa->estado = $estadoRecibido;
        $mesa->codigoAlfanumerico = $codigoAlfanumerico;
        $mesa->crearMesa();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
