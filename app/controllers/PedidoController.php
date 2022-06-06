<?php
require_once './models/Pedido.php';
//require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido //implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {

        $parametros = $request->getParsedBody();

        $codigoAlfaNumericoRecibido = $parametros['codigoAlfaNumerico'];

        // Creamos el nuevo pedido
        $pedido = new Pedido();
        $pedido->codigoAlfaNumerico = $codigoAlfaNumericoRecibido;
        $pedido->productosPedidos = $productosPedidosRecibido;
        $pedido->crearPedido();

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
