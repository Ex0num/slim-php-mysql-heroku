<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Mesa as Mesa;
date_default_timezone_set("America/Buenos_Aires");

class MesaController implements IApiUsable
{

    public function CargarUno($request, $response, $args)
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

            //------------------------- RECOPILACION LA INFORMACION DE LA ACCION REALIZADA Y SU RESULTADO --------------------------------//
            //Retorno la respuesta con el body escrito el cual contiene el mensaje de la accion, si hubo o no exito y todos los ids de
            //las entidades involucradas en la accion con un valor cargado, en su defecto, nulo.
            $payload = json_encode(array("mensajeFinal" => "Mesa creada con exito",
            "exito" => "exitoso","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => $mesaCreada->id, "idPedido" => null,"idVenta" => null));
            //-----------------------------------------------------------------------------------------------------------------------------//
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Mesa creada sin exito. No posee los permisos.",
            "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
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
            $mesaEncontrada = App\Models\Mesa::find($idRecibida);
                
            if ($mesaEncontrada != null)
            {
                //El ORM borra automaticamente el usuario en la DB.
                $mesaEncontrada->delete();

                $payload = json_encode(array("mensajeFinal" => "Mesa borrada con exito.",
                "exito" => "exitoso","tipo" => "baja","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => $mesaEncontrada->id, "idPedido" => null,"idVenta" => null)); 
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "Mesa borrada sin exito. No se encontró la mesa.",
                "exito" => "fallido","tipo" => "baja","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Mesa borrada sin exito. No posee permisos.",
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

            //Busco en la DB en el campo 'id' el valor del ID recibido y me guardo a la mesa encontrada si es que se encontró alguna.
            $mesaEncontrada = App\Models\Mesa::where('id', '=', $id)->first();
            
            //Leo el json "RAW" de Postman y hago el decode de los datos nuevos para modificar al usuario .
            $body = json_decode(file_get_contents("php://input"), true);

            if ($mesaEncontrada != null)
            {
                $numeroRecibido = $body['numero'];
                $estadoRecibido = $body['estado'];
                $descripcionRecibida = $body['descripcion'];

                //Si se intenta cambiar el estado de la mesa a "cerrada" y el que lo esta cambiando es un mozo. Error. No puede.
                if ($estadoRecibido == "cerrada" && $tipoUsuarioResponsable == "mozo")
                {
                    $horaActual = date('h:i:s');

                    $payload = json_encode(array("mensajeFinal" => "Mesa modificada sin exito. No se encontro la mesa a modificar.",
                    "exito" => "fallido","tipo" => "modificacion","hora" => $horaActual,"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));

                    //Retorno la respuesta con el body que contiene un mensaje.
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    //Piso los datos 'viejos' por los 'nuevos' datos de la mesa a modificar.
                    $mesaEncontrada->numero = $numeroRecibido;
                    $mesaEncontrada->estado = $estadoRecibido;
                    $mesaEncontrada->descripcion = $descripcionRecibida;
                
                    //El ORM guarda automaticamente la mesa en la DB.
                    $mesaEncontrada->save();

                    $payload = json_encode(array("mensajeFinal" => "Mesa modificada con exito.",
                    "exito" => "exitoso","tipo" => "modificacion","hora" => date('h:i:s'),
                    "idUsuarioResponsable" => $idUsuarioResponsable, "idUsuario" => null,
                    "idProducto" => null, "idMesa" => $mesaEncontrada->id, "idPedido" => null,"idVenta" => null));

                    //Retorno la respuesta con el body que contiene un mensaje.
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }   
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "Mesa modificada sin exito. No se encontro la mesa a modificar.",
                "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Mesa modificada sin exito. No posee permisos.",
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
            //Me traigo a todas las mesas.
            $listaMesas = App\Models\Mesa::all();
        
            if (count($listaMesas) <= 0)
            {
                $payload = json_encode(array("mensajeFinal" => "No se encontro ninguna mesa.",
                "exito" => "fallido","tipo" => "listarTodos","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
            }
            else
            {
                //Voy a devolver como texto dentro de la respuesta a las mesas encontrados.
                $JsonMesas = json_encode(array("listaMesas" => $listaMesas));
                
                //Imprimo todas las mesas.
                echo($JsonMesas);

                $payload = json_encode(array("mensajeFinal" => "Se listaron todas las mesas.",
                "exito" => "exitoso","tipo" => "listarTodos","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "El listado de todas las mesas no fue posible. No posee permisos.",
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

            //Me traigo a todas las mesas y busco a la mesa con el ID recibido.
            $listaMesas = Mesa::all();
            $mesaEncontrada = $listaMesas->find($idRecibido);

            if($mesaEncontrada != null)
            {
                //Voy a devolver como texto dentro de la respuesta a la mesa encontrada.
                $JsonMesa = json_encode($mesaEncontrada);

                //Imprimo la mesa.
                echo($JsonMesa);

                $payload = json_encode(array("mensajeFinal" => "El listar de una mesa fue realizado.",
                "exito" => "exitoso","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => $mesaEncontrada->id, "idPedido" => null,"idVenta" => null));
            }    
            else
            {
                $payload = json_encode(array("mensajeFinal" => "El listar de una mesa no fue posible. No se encontro la mesa.",
                "exito" => "fallido","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "El listar de una mesa no fue posible. No posee permisos.",
            "exito" => "fallido","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }
        
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>