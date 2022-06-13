<?php
require_once './models/Pedido.php';
require_once './models/Venta.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Venta as Venta;

class PedidoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        //Leo el json "RAW" de Postman y hago el decode de los datos nuevos para leer los productos del pedido.
        $body = json_decode(file_get_contents("php://input"), true);

        //Obtengo toda la data necesaria.
        $idMesaRecibido = $body['idMesa'];
        $idUsuarioRecibido = $body['idUsuario'];
        $nombreClienteRecibido = $body['nombreCliente'];
        $pathFoto = $body['pathFoto'];
        $productosPedidos = $body['productosPedidos'];

        //Busco la ID recibida y me guardo a la mesa encontrada si es que se encontró alguna.
        $mesaEncontrada = App\Models\Mesa::find($idMesaRecibido);

        //Busco la ID recibida y me guardo al usuario encontrado si es que se encontró alguno.
        $usuarioEncontrado = App\Models\Usuario::find($idUsuarioRecibido);

        var_dump($productosPedidos);

        //TIENE QUE SI O SI HABER UN USUARIO, UNA MESA Y MINIMO UN PRODUCTO O MAS.
        if ($mesaEncontrada != null && $usuarioEncontrado != null && count($productosPedidos) > 0)
        {

            //FALTA VALIDAR STOCK DISPONIBLE DE PRODUCTOS

            //-------------------------------- CREACION DEL PEDIDO ---------------------------------------------//

            //Creo el codigo alfanumerico del pedido.
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
            $codigoAlfanumericoCreado = substr(str_shuffle($permitted_chars), 0, 10);

            //Calculo el precio total en cuestion de los productos recibidos y el tiempo estimado.
            $precioTotalCalculado = 890; //Aca va funcion calculadora
            $tiempoEstimadoCalculado = 5; //Aca va funcion calculadora

            // Creo el pedido y asigno sus correspondientes datos.
            $pedidoCreado = new Pedido();

            $pedidoCreado->codigoAlfanumerico = $codigoAlfanumericoCreado;
            $pedidoCreado->codigoAlfanumericoMesa = $mesaEncontrada->codigoAlfanumerico;
            $pedidoCreado->estado = "Pendiente";
            $pedidoCreado->precioTotal = $precioTotalCalculado;
            $pedidoCreado->minutosEstimados = $tiempoEstimadoCalculado;
            $pedidoCreado->nroMesa = $mesaEncontrada->numero;
            $pedidoCreado->nombreCliente = $nombreClienteRecibido;
            $pedidoCreado->idMesa = $mesaEncontrada->id;
            $pedidoCreado->idUsuario = $usuarioEncontrado->id;

            //El ORM guarda automaticamente el pedido en la DB.
            $pedidoCreado->save();

            //-------------------- CREACION DE LAS VENTAS DEL PEDIDO POR CADA PRODUCTO PEDIDO ------------------//

            foreach ($productosPedidos as $producto) 
            {
                $ventaCreada = new Venta();

                $ventaCreada->cantidad = $producto["cantidadProducto"];
                $ventaCreada->estado = "preparandose";
                $ventaCreada->tiempoEstimado = 10;
                $ventaCreada->horaPreparacion = date('h:i:s');
                $ventaCreada->idProducto = $producto["idProducto"];
                $ventaCreada->idPedido = $pedidoCreado->id;
                $ventaCreada->idUsuario = 41;

                //El ORM guarda automaticamente el pedido en la DB.
                $ventaCreada->save();
            }

            $payload = json_encode(array("mensaje" => "Pedido creado con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Pedido no pudo ser creado"));
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $idRecibida = $args['id'];

        //Busco la ID recibida y me guardo al pedido encontrado si es que se encontró alguno.
        $pedidoEncontrado = App\Models\Pedido::find($idRecibida);
            
        if ($pedidoEncontrado != null)
        {
            //El ORM borra automaticamente el pedido en la DB.
            $pedidoEncontrado->delete();
            $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Pedido no eliminado")); 
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        // //Recibo la ID por el "link".
        // $id = $args['id'];

        // //Busco en la DB en el campo 'id' el valor del ID recibido y me guardo a la mesa encontrada si es que se encontró alguna.
        // $mesaEncontrada = App\Models\Pedido::where('id', '=', $id)->first();
        
        // //Leo el json "RAW" de Postman y hago el decode de los datos nuevos para modificar al usuario .
        // $body = json_decode(file_get_contents("php://input"), true);

        // if ($mesaEncontrada != null)
        // {
        //     $numeroRecibido = $body['numero'];
        //     $estadoRecibido = $body['estado'];
        //     $descripcionRecibida = $body['descripcion'];

        //     //Piso los datos 'viejos' por los 'nuevos' datos de la mesa a modificar.
        //     $mesaEncontrada->numero = $numeroRecibido;
        //     $mesaEncontrada->estado = $estadoRecibido;
        //     $mesaEncontrada->descripcion = $descripcionRecibida;
        
        //     //El ORM guarda automaticamente la mesa en la DB.
        //     $mesaEncontrada->save();
        //     $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
        // }
        // else
        // {
        //     $payload = json_encode(array("mensaje" => "Mesa no modificada")); 
        // }

        // //Retorno la respuesta con el body que contiene un mensaje.
        // $response->getBody()->write($payload);
        // return $response->withHeader('Content-Type', 'application/json');    
    }

    public function TraerTodos($request, $response, $args)
    {
        //Me traigo a todos los pedidos.
        $listaPedidos = App\Models\Pedido::all();
    
        if (count($listaPedidos) <= 0)
        {
            $payload = json_encode(array("mensaje" => "No existe ningun pedido."));   
        }
        else
        {
            //Voy a devolver como texto dentro de la respuesta a los pedidos encontrados.
            $payload = json_encode(array("listaPedidos" => $listaPedidos));
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $idRecibido = $args['id'];

        //Me traigo a todos los pedidos y busco al pedido con el ID recibido.
        $listaPedidos = Pedido::all();
        $pedidoEncontrado = $listaPedidos->find($idRecibido);
        
        if ($pedidoEncontrado != null)
        {
            //Voy a devolver como texto dentro de la respuesta al pedido encontrado.
            $payload = json_encode($pedidoEncontrado);
        } 
        else
        {
            $payload = json_encode(array("mensaje" => "Pedido no encontrado."));
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
