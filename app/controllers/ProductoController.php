<?php
require_once './models/Producto.php';
//require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto //implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombreRecibido = $parametros['nombre'];
        $precioRecibido = $parametros['precio'];
        $tipoRecibido = $parametros['tipo'];
        $stockRecibido = $parametros['stock'];

        // Creamos el nuevo producto
        $producto = new Producto();
        $producto->nombre = $nombreRecibido;
        $producto->precio = $precioRecibido;
        $producto->tipo = $tipoRecibido;
        $producto->stock = $stockRecibido;
        $producto->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}