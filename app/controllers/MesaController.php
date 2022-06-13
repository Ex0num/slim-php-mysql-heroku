<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Mesa as Mesa;

class MesaController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        //Recibo el body del form-data en forma de array asociativo.
        $parametros = $request->getParsedBody();

        $numeroRecibido = $parametros['numero'];
        $estadoRecibido = $parametros['estado'];
        $descripcionRecibida = $parametros['descripcion'];

        //Creo el codigo alfanumerico de la mesa.
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $codigoAlfanumericoCreado = substr(str_shuffle($permitted_chars), 0, 10);

        // Creo la mesa y asigno sus correspondientes datos.
        $mesaCreada = new Mesa();

        $mesaCreada->numero = $numeroRecibido;
        $mesaCreada->estado = $estadoRecibido;
        $mesaCreada->descripcion = $descripcionRecibida;
        $mesaCreada->codigoAlfanumerico = $codigoAlfanumericoCreado;

        //El ORM guarda automaticamente la mesa en la DB.
        $mesaCreada->save();

        //Retorno la respuesta con el body que contiene un mensaje.
        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $idRecibida = $args['id'];

        //Busco la ID recibida y me guardo al usuario encontrado si es que se encontró alguno.
        $mesaEncontrada = App\Models\Mesa::find($idRecibida);
            
        if ($mesaEncontrada != null)
        {
            //El ORM borra automaticamente el usuario en la DB.
            $mesaEncontrada->delete();
            $payload = json_encode(array("mensaje" => "Mesa borrada con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Mesa no eliminada")); 
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $id = $args['id'];

        //Busco en la DB en el campo 'id' el valor del ID recibido y me guardo a la mesa encontrada si es que se encontró alguna.
        $mesaEncontrada = App\Models\Mesa::where('id', '=', $id)->first();
        
        //Leo el json "RAW" de Postman y hago el decode de los datos nuevos para modificar al usuario .
        $body = json_decode(file_get_contents("php://input"), true);

        if ($mesaEncontrada != null)
        {
            $numeroRecibido = $body['numero'];
            $estadoRecibido = $body['estado'];
            $descripcionRecibida = $body['descripcion'];

            //Piso los datos 'viejos' por los 'nuevos' datos de la mesa a modificar.
            $mesaEncontrada->numero = $numeroRecibido;
            $mesaEncontrada->estado = $estadoRecibido;
            $mesaEncontrada->descripcion = $descripcionRecibida;
        
            //El ORM guarda automaticamente la mesa en la DB.
            $mesaEncontrada->save();
            $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Mesa no modificada")); 
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');    
    }

    public function TraerTodos($request, $response, $args)
    {
        //Me traigo a todas las mesas.
        $listaMesas = App\Models\Mesa::all();
    
        if ($listaMesas.length() <= 0)
        {
            $payload = json_encode(array("mensaje" => "No hay ninguna mesa."));     
        }
        else
        {
            //Voy a devolver como texto dentro de la respuesta a las mesas encontrados.
            $payload = json_encode(array("listaMesas" => $listaMesas));
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        //Recibo la ID por el "link".
        $idRecibido = $args['id'];

        //Me traigo a todas las mesas y busco a la mesa con el ID recibido.
        $listaMesas = Mesa::all();
        $mesaEncontrada = $listaMesas->find($idRecibido);

        if($mesaEncontrada != null)
        {
            //Voy a devolver como texto dentro de la respuesta a la mesa encontrada.
            $payload = json_encode($mesaEncontrada);
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

?>