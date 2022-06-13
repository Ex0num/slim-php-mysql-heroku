<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Producto as Producto;

class ProductoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        //Recibo el body del form-data en forma de array asociativo.
        $parametros = $request->getParsedBody();

        $nombreRecibido = $parametros['nombre'];
        $precioRecibido = $parametros['precio'];
        $tiempoPromedioRecibido = $parametros['tiempoMinutos'];
        $areaResponsableRecibida = $parametros['area'];
        $tipoRecibido = $parametros['tipo'];
        $stockRecibido = $parametros['stock'];

        // Creo el producto y asigno sus correspondientes datos.
        $productoCreado = new Producto();

        $productoCreado->nombre = $nombreRecibido;
        $productoCreado->precio = $precioRecibido;
        $productoCreado->tiempoMinutos = $tiempoPromedioRecibido;
        $productoCreado->area = $areaResponsableRecibida;
        $productoCreado->tipo = $tipoRecibido;
        $productoCreado->stock = $stockRecibido;

        //El ORM guarda automaticamente el producto en la DB.
        $productoCreado->save();

        //Retorno la respuesta con el body que contiene un mensaje.
        $payload = json_encode(array("mensaje" => "Producto creado con exito"));
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $idRecibida = $args['id'];

        //Busco la ID recibida y me guardo al producto encontrado si es que se encontró alguno.
        $productoEncontrado = App\Models\Producto::find($idRecibida);
            
        if ($productoEncontrado != null)
        {
            //El ORM borra automaticamente el producto en la DB.
            $productoEncontrado->delete();
            $payload = json_encode(array("mensaje" => "Producto borrado con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Producto no eliminado")); 
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $id = $args['id'];

        //Busco en la DB en el campo 'id' el valor del ID recibido y me guardo al producto encontrado si es que se encontró alguno.
        $productoEncontrado = App\Models\Producto::where('id', '=', $id)->first();
        
        //Leo el json "RAW" de Postman y hago el decode de los datos nuevos para modificar al producto.
        $body = json_decode(file_get_contents("php://input"), true);

        if ($productoEncontrado != null)
        {
            $nombreRecibido = $body['nombre'];
            $precioRecibido = $body['precio'];
            $tiempoPromedioRecibido = $body['tiempoMinutos'];
            $areaResponsableRecibida = $body['area'];
            $tipoRecibido = $body['tipo'];
            $stockRecibido = $body['stock'];

            //Piso los datos 'viejos' por los 'nuevos' datos del producto a modificar.
            $productoEncontrado->nombre = $nombreRecibido;
            $productoEncontrado->precio = $precioRecibido;
            $productoEncontrado->tiempoMinutos = $tiempoPromedioRecibido;
            $productoEncontrado->area = $areaResponsableRecibida;
            $productoEncontrado->tipo = $tipoRecibido;
            $productoEncontrado->stock = $stockRecibido;
        
            //El ORM guarda automaticamente la producto en la DB.
            $productoEncontrado->save();
            $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Producto no modificado")); 
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');    
    }

    public function TraerTodos($request, $response, $args)
    {
        //Me traigo a todas los productos.
        $listaProductos = App\Models\Producto::all();
    
        //Voy a devolver como texto dentro de la respuesta a los productos encontrados.
        $payload = json_encode(array("listaProductos" => $listaProductos));

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $idRecibido = $args['id'];

        //Me traigo a todas los productos y busco al producto con el ID recibido.
        $listaProductos = Producto::all();
        $productoEncontrado = $listaProductos->find($idRecibido);

        //Voy a devolver como texto dentro de la respuesta al producto encontrado.
        $payload = json_encode($productoEncontrado);

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}