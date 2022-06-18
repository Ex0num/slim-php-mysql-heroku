<?php
require_once './models/Mesa.php';
require_once './models/Producto.php';
require_once './models/Usuario.php';
require_once './models/Pedido.php';
require_once './models/FilesManagement.php';
date_default_timezone_set("America/Buenos_Aires");

class CSV_Controller
{

    public function CargarProductosCSV($request, $response, $args)
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
            //Recibo el nombre en crudo
            $nombreCSVRecibido = "CSV/".$_FILES["productosCSV"]["name"];
            
            //Le saco la extension o todo lo que tenga despues de un "."
            $nombreCSVReciciboSinExt = explode(".",$nombreCSVRecibido);

            //Con esa parte excluida del ".", le voy a agregar la extension csv"
            $destino = $nombreCSVReciciboSinExt[0] .".csv";

            //Muevo el archivo y uplodeo la DB
            move_uploaded_file($_FILES["productosCSV"]["tmp_name"],$destino);
            
            $lecturaSalioBien = FilesManagement::LeerProductosCSV($destino);

            if ($lecturaSalioBien == true)
            {
                $payload = json_encode(array("mensajeFinal" => "La carga de productos fue realizada",
                "exito" => "exitoso","tipo" => "carga","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "La carga de productos no fue realizada. Hubo un error en la lectura del CSV. (Revise comas y campos).",
                "exito" => "fallido","tipo" => "carga","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
        }
        else
        {
            if ($tipoUsuarioResponsable != "socio")
            {
                $payload = json_encode(array("mensajeFinal" => "La carga de productos no fue realizada. No posee los permisos.",
                "exito" => "fallido","tipo" => "carga","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
            else if ($_FILES["productosCSV"] != null)
            {
                $payload = json_encode(array("mensajeFinal" => "La carga de productos no fue realizada. No se recibio un archivo CSV.",
                "exito" => "fallido","tipo" => "carga","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));   
            }   
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function DescargarProductosCSV($request, $response, $args)
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
            // -------------------Me traigo al archivo al que voy a escribir----------------///

            //Recibo el nombre en crudo
            $nombreCSVRecibido = "CSV/" . $_FILES["productosCSV"]["name"];
            
            //Le saco la extension o todo lo que tenga despues de un "."
            $nombreCSVReciciboSinExt = explode(".",$nombreCSVRecibido);

            var_dump($nombreCSVReciciboSinExt);

            //Con esa parte excluida del ".", le voy a agregar la extension csv"
            $destino = $nombreCSVReciciboSinExt[0] .".csv";

            //Muevo el archivo y uplodeo la DB
            move_uploaded_file($_FILES["productosCSV"]["tmp_name"],$destino);
            //------------------------------------------------------------------------------//

            $listaProductosDB = App\Models\Producto::all();

            if (count($listaProductosDB) > 0)
            {
                try
                {
                    $escrituraSalioBien = FilesManagement::EscribirProductosCSV($destino,$listaProductosDB);
                    $escrituraSalioBien = true;

                    $payload = json_encode(array("mensajeFinal" => "La descarga de productos fue realizada.",
                    "exito" => "exitoso","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  

                }
                catch (Exception $e)
                {
                    $payload = json_encode(array("mensajeFinal" => "La descarga de productos no fue realizada. No hay ningun producto en DB.",
                    "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  
                }
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "La descarga de productos no fue realizada. No hay ningun producto en DB.",
                "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "La descarga de productos no fue realizada. No posee los permisos.",
            "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');   
    }
}
?>