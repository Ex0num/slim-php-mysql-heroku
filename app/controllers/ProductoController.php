<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Producto as Producto;
date_default_timezone_set("America/Buenos_Aires");

class ProductoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//
        if (($tipoUsuarioResponsable == "socio" || $tipoUsuarioResponsable == "mozo") == true && $estadoUsuarioResponsable == "activo")
        {
            //Recibo el body del form-data en forma de array asociativo.
            $parametros = $request->getParsedBody();

            $nombreRecibido = $parametros['nombre'];
            $precioRecibido = $parametros['precio'];
            $tiempoPromedioRecibido = $parametros['tiempoMinutos'];
            $areaResponsableRecibida = $parametros['area'];
            $tipoRecibido = $parametros['tipo'];
            $stockRecibido = $parametros['stock'];

            $resultadoValidacionNombre =  Validaciones::validarNombre_Producto($nombreRecibido);
            $resultadoValidacionPrecio =  Validaciones::validarPrecio_Producto($precioRecibido);
            $resultadoValidacionTiempoPromedio =  Validaciones::validarTiempo_Producto($tiempoPromedioRecibido);
            $resultadoValidacionArea =  Validaciones::validarArea_Producto($areaResponsableRecibida);
            $resultadoValidacionTipo =  Validaciones::validarTipo_Producto($tipoRecibido);
            $resultadoValidacionStock =  Validaciones::validarStock_Producto($stockRecibido);

            if ($resultadoValidacionNombre == true && $resultadoValidacionPrecio == true && $resultadoValidacionArea == true && 
            $resultadoValidacionTipo == true && $resultadoValidacionStock == true)
            {
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
                $payload = json_encode(array("mensajeFinal" => "Producto creado con exito.",
                "exito" => "exitoso","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => $productoCreado->id, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "Producto creado sin exito. Hubo algun dato invalido.",
                "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Producto creado sin exito. No posee los permisos.",
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
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//
        if (($tipoUsuarioResponsable == "socio" || $tipoUsuarioResponsable == "mozo") == true && $estadoUsuarioResponsable == "activo")
        {
            //Recibo la ID por el "link".
            $idRecibida = $args['id'];

            //Busco la ID recibida y me guardo al producto encontrado si es que se encontró alguno.
            $productoEncontrado = App\Models\Producto::find($idRecibida);
                
            if ($productoEncontrado != null)
            {
                //El ORM borra automaticamente el producto en la DB.
                $productoEncontrado->delete();

                $payload = json_encode(array("mensajeFinal" => "Producto borrado con exito.",
                "exito" => "exitoso","tipo" => "baja","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => $productoEncontrado->id, "idMesa" => null, "idPedido" => null,"idVenta" => null));  
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "Producto borrado sin exito. No se pudo encontrar.",
                "exito" => "fallido","tipo" => "baja","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Producto borrado sin exito. No posee los permisos.",
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
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//
        if ($tipoUsuarioResponsable == "socio" && $estadoUsuarioResponsable == "activo")
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

                $resultadoValidacionNombre =  Validaciones::validarNombre_Producto($nombreRecibido);
                $resultadoValidacionPrecio =  Validaciones::validarPrecio_Producto($precioRecibido);
                $resultadoValidacionTiempoPromedio =  Validaciones::validarTiempo_Producto($tiempoPromedioRecibido);
                $resultadoValidacionArea =  Validaciones::validarArea_Producto($areaResponsableRecibida);
                $resultadoValidacionTipo =  Validaciones::validarTipo_Producto($tipoRecibido);
                $resultadoValidacionStock =  Validaciones::validarStock_Producto($stockRecibido);

                if ($resultadoValidacionNombre == true && $resultadoValidacionPrecio == true && $resultadoValidacionTiempoPromedio == true &&
                $resultadoValidacionArea == true && $resultadoValidacionTipo == true && $resultadoValidacionStock == true)
                {
                    //Piso los datos 'viejos' por los 'nuevos' datos del producto a modificar.
                    $productoEncontrado->nombre = $nombreRecibido;
                    $productoEncontrado->precio = $precioRecibido;
                    $productoEncontrado->tiempoMinutos = $tiempoPromedioRecibido;
                    $productoEncontrado->area = $areaResponsableRecibida;
                    $productoEncontrado->tipo = $tipoRecibido;
                    $productoEncontrado->stock = $stockRecibido;
                
                    //El ORM guarda automaticamente la producto en la DB.
                    $productoEncontrado->save();

                    $payload = json_encode(array("mensajeFinal" => "Producto modificado con exito.",
                    "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => $productoEncontrado->id, "idMesa" => null, "idPedido" => null,"idVenta" => null));
                }
                else
                {
                    $payload = json_encode(array("mensajeFinal" => "Producto modificado sin exito. Hubo algun dato invalido.",
                    "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => $productoEncontrado->id, "idMesa" => null, "idPedido" => null,"idVenta" => null));
                }
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "Producto modificado sin exito. No se pudo encontrar.",
                "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Producto modificado sin exito. No posee los permisos.",
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
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio y mozo
        //----------------------------------------------------------------------------------//
        if (($tipoUsuarioResponsable == "socio" || $tipoUsuarioResponsable == "mozo") == true && $estadoUsuarioResponsable == "activo")
        {
            //Me traigo a todas los productos.
            $listaProductos = App\Models\Producto::all();
        
            //Voy a devolver como texto dentro de la respuesta a los productos encontrados.
            $payload = json_encode(array("mensajeFinal" => "Se listaron todas los productos.",
            "exito" => "exitoso","tipo" => "listarTodos","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));   
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "El listado de todas los productos no fue posible. No posee permisos.",
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
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio y mozo
        //----------------------------------------------------------------------------------//
        if (($tipoUsuarioResponsable == "socio" || $tipoUsuarioResponsable == "mozo") == true && $estadoUsuarioResponsable == "activo")
        {
            //Recibo la ID por el "link".
            $idRecibido = $args['id'];

            //Me traigo a todas los productos y busco al producto con el ID recibido.
            $listaProductos = Producto::all();
            $productoEncontrado = $listaProductos->find($idRecibido);

            //Voy a devolver como texto dentro de la respuesta al producto encontrado.
            $JsonProducto = json_encode($productoEncontrado);

            //Imprimo el producto.
            echo($JsonProducto);

            $payload = json_encode(array("mensajeFinal" => "El listar de un producto fue realizado.",
            "exito" => "exitoso","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => $productoEncontrado->id, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
        }   
        else
        {
            $payload = json_encode(array("mensajeFinal" => "El listar de un producto no fue posible. No posee permisos.",
            "exito" => "fallido","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }

        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}