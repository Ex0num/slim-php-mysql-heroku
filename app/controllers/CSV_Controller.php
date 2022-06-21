<?php
require_once './models/Mesa.php';
require_once './models/Producto.php';
require_once './models/Usuario.php';
require_once './models/Pedido.php';
require_once './models/Accion.php';
require_once './models/FilesManagement.php';

date_default_timezone_set("America/Buenos_Aires");

class CSV_Controller
{

    //------------------------ PRODUCTOS ----------------------------//
    public function Productos_CSV_a_DB($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//

        if ($tipoUsuarioResponsable == "socio" && $estadoUsuarioResponsable == "activo")
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

    public function Productos_DB_a_CSV($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//

        if ($tipoUsuarioResponsable == "socio" && $estadoUsuarioResponsable == "activo")
        {
            // -------------------Me traigo al archivo al que voy a escribir----------------///

            //Recibo el nombre en crudo
            $nombreCSVRecibido = "CSV/" . $_FILES["productosCSV"]["name"];
            
            //Le saco la extension o todo lo que tenga despues de un "."
            $nombreCSVReciciboSinExt = explode(".",$nombreCSVRecibido);

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
                    $payload = json_encode(array("mensajeFinal" => "La descarga de productos no fue realizada. Hubo un error en la lectura.",
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

    //---------------------------USUARIOS----------------------------//
    public function Usuarios_DB_a_CSV($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//

        if ($tipoUsuarioResponsable == "socio" && $estadoUsuarioResponsable == "activo")
        {
            // -------------------Me traigo al archivo al que voy a escribir----------------///

            //Recibo el nombre en crudo
            $nombreCSVRecibido = "CSV/" . $_FILES["usuariosCSV"]["name"];
            
            //Le saco la extension o todo lo que tenga despues de un "."
            $nombreCSVReciciboSinExt = explode(".",$nombreCSVRecibido);

            //Con esa parte excluida del ".", le voy a agregar la extension csv"
            $destino = $nombreCSVReciciboSinExt[0] .".csv";

            //Muevo el archivo y uplodeo la DB
            move_uploaded_file($_FILES["usuariosCSV"]["tmp_name"],$destino);
            //------------------------------------------------------------------------------//

            $listaUsuariosDB = App\Models\Usuario::all();

            if (count($listaUsuariosDB) > 0)
            {
                try
                {
                    $escrituraSalioBien = FilesManagement::EscribirUsuariosCSV($destino,$listaUsuariosDB);
                    $escrituraSalioBien = true;

                    $payload = json_encode(array("mensajeFinal" => "La descarga de usuarios fue realizada.",
                    "exito" => "exitoso","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  

                }
                catch (Exception $e)
                {
                    $payload = json_encode(array("mensajeFinal" => "La descarga de usuarios no fue realizada. Hubo un error en la lectura",
                    "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  
                }
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "La descarga de usuarios no fue realizada. No hay ningun usuario en DB.",
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

    //---------------------------ACCIONES----------------------------//
    public function Acciones_DB_a_CSV($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//

        if ($tipoUsuarioResponsable == "socio" && $estadoUsuarioResponsable == "activo")
        {
            // -------------------Me traigo al archivo al que voy a escribir----------------///

            //Recibo el nombre en crudo
            $nombreCSVRecibido = "CSV/" . $_FILES["accionesCSV"]["name"];
            
            //Le saco la extension o todo lo que tenga despues de un "."
            $nombreCSVReciciboSinExt = explode(".",$nombreCSVRecibido);

            //Con esa parte excluida del ".", le voy a agregar la extension csv"
            $destino = $nombreCSVReciciboSinExt[0] .".csv";

            //Muevo el archivo y uplodeo la DB
            move_uploaded_file($_FILES["accionesCSV"]["tmp_name"],$destino);
            //------------------------------------------------------------------------------//

            $listaAccionesDB = App\Models\Accion::all();

            if (count($listaAccionesDB) > 0)
            {
                try
                {
                    $escrituraSalioBien = FilesManagement::EscribirAccionesCSV($destino,$listaAccionesDB);
                    $escrituraSalioBien = true;

                    $payload = json_encode(array("mensajeFinal" => "La descarga de acciones fue realizada.",
                    "exito" => "exitoso","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  

                }
                catch (Exception $e)
                {
                    $payload = json_encode(array("mensajeFinal" => "La descarga de acciones no fue realizada. No hay ninguna accion en DB.",
                    "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  
                }
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "La descarga de acciones no fue realizada. No hay ninguna accion en DB.",
                "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "La descarga de acciones no fue realizada. No posee los permisos.",
            "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');   
    }

    //---------------------------PEDIDOS-----------------------------//
    public function Pedidos_DB_a_CSV($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//

        if ($tipoUsuarioResponsable == "socio" && $estadoUsuarioResponsable == "activo")
        {
            // -------------------Me traigo al archivo al que voy a escribir----------------///

            //Recibo el nombre en crudo
            $nombreCSVRecibido = "CSV/" . $_FILES["pedidosCSV"]["name"];
            
            //Le saco la extension o todo lo que tenga despues de un "."
            $nombreCSVReciciboSinExt = explode(".",$nombreCSVRecibido);

            //Con esa parte excluida del ".", le voy a agregar la extension csv"
            $destino = $nombreCSVReciciboSinExt[0] .".csv";

            //Muevo el archivo y uplodeo la DB
            move_uploaded_file($_FILES["pedidosCSV"]["tmp_name"],$destino);
            //------------------------------------------------------------------------------//

            $listaPedidosDB = App\Models\Pedido::all();

            if (count($listaPedidosDB) > 0)
            {
                try
                {
                    $escrituraSalioBien = FilesManagement::EscribirPedidosCSV($destino,$listaPedidosDB);
                    $escrituraSalioBien = true;

                    $payload = json_encode(array("mensajeFinal" => "La descarga de pedidos fue realizada.",
                    "exito" => "exitoso","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  

                }
                catch (Exception $e)
                {
                    $payload = json_encode(array("mensajeFinal" => "La descarga de pedidos no fue realizada. No hay ningun pedido en DB.",
                    "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  
                }
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "La descarga de pedidos no fue realizada. No hay ningun pedido en DB.",
                "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "La descarga de pedidos no fue realizada. No posee los permisos.",
            "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');   
    }

    //---------------------------MESAS-------------------------------//
    public function Mesas_DB_a_CSV($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//

        if ($tipoUsuarioResponsable == "socio" && $estadoUsuarioResponsable == "activo")
        {
            // -------------------Me traigo al archivo al que voy a escribir----------------///

            //Recibo el nombre en crudo
            $nombreCSVRecibido = "CSV/" . $_FILES["mesasCSV"]["name"];
            
            //Le saco la extension o todo lo que tenga despues de un "."
            $nombreCSVReciciboSinExt = explode(".",$nombreCSVRecibido);

            //Con esa parte excluida del ".", le voy a agregar la extension csv"
            $destino = $nombreCSVReciciboSinExt[0] .".csv";

            //Muevo el archivo y uplodeo la DB
            move_uploaded_file($_FILES["mesasCSV"]["tmp_name"],$destino);
            //------------------------------------------------------------------------------//

            $listaMesasDB = App\Models\Mesa::all();

            if (count($listaMesasDB) > 0)
            {
                try
                {
                    $escrituraSalioBien = FilesManagement::EscribirMesasCSV($destino,$listaMesasDB);
                    $escrituraSalioBien = true;

                    $payload = json_encode(array("mensajeFinal" => "La descarga de mesas fue realizada.",
                    "exito" => "exitoso","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  

                }
                catch (Exception $e)
                {
                    $payload = json_encode(array("mensajeFinal" => "La descarga de mesas no fue realizada. No hay ninguna mesa en DB.",
                    "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  
                }
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "La descarga de mesas no fue realizada. No hay ninguna mesa en DB.",
                "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "La descarga de mesas no fue realizada. No posee los permisos.",
            "exito" => "fallido","tipo" => "lectura","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');   
    }
}
?>