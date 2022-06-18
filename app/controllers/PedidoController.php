<?php
require_once './models/Pedido.php';
require_once './models/Venta.php';
require_once './models/Usuario.php';
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Pedido as Pedido;
use \App\Models\Venta as Venta;
date_default_timezone_set("America/Buenos_Aires");

class PedidoController implements IApiUsable
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
        if ($tipoUsuarioResponsable == "socio" || $tipoUsuarioResponsable == "mozo")
        {
            //Leo el json "RAW" de Postman y hago el decode de los datos nuevos para leer los productos del pedido.
            $body = json_decode(file_get_contents("php://input"), true);

            //Obtengo toda la data necesaria.
            $idMesaRecibido = $body['idMesa'];
            $nombreClienteRecibido = $body['nombreCliente'];
            $productosPedidos = $body['productosPedidos'];

            //Busco la ID recibida y me guardo a la mesa encontrada si es que se encontró alguna.
            $mesaEncontrada = App\Models\Mesa::find($idMesaRecibido);

            // Valido la lista de productos adquiridos en el pedido, si cada uno de ellos tiene el stock suficiente para la cantidad 
            // que se desea adquirir. ---> Le paso el array asociativo de productos recibido por body
            $validacionTotalDeProductos = $this->validarTotalmenteProductosPedidos($productosPedidos);

            //Valido si todos los productos de la lista tienen una persona en su correspondiente area para hacerse cargo. 
            $validacionDisponibilidadPersonal = $this->validacionDisponibilidadPersonal($productosPedidos);

            //TIENE QUE SI O SI HABER UN USUARIO, UNA MESA Y MINIMO UN PRODUCTO O MAS.
            if ($mesaEncontrada != null && count($productosPedidos) > 0 && $validacionTotalDeProductos == 0 && $validacionDisponibilidadPersonal == 0)
            {

                //-------------------------------- CREACION DEL PEDIDO ---------------------------------------------//
                
                //Creo el codigo alfanumerico del pedido.
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                $codigoAlfanumericoCreado = substr(str_shuffle($permitted_chars), 0, 10);

                //Calculo el precio total en cuestion de los productos recibidos y el tiempo estimado.
                $precioTotalCalculado = $this->calcularPrecioTotal($productosPedidos);
                $tiempoEstimadoCalculado = $this->calcularTiempoEstimado($productosPedidos);
        
                // Creo el pedido y asigno sus correspondientes datos.
                $pedidoCreado = new Pedido();

                $pedidoCreado->codigoAlfanumerico = $codigoAlfanumericoCreado;
                $pedidoCreado->codigoAlfanumericoMesa = $mesaEncontrada->codigoAlfanumerico;
                $pedidoCreado->estado = "pendiente";
                $pedidoCreado->precioTotal = $precioTotalCalculado;
                $pedidoCreado->minutosEstimados = $tiempoEstimadoCalculado;
                $pedidoCreado->nroMesa = $mesaEncontrada->numero;
                $pedidoCreado->nombreCliente = $nombreClienteRecibido;
                $pedidoCreado->idMesa = $mesaEncontrada->id;
                $pedidoCreado->idUsuario = $idUsuarioResponsable;

                //El ORM guarda automaticamente el pedido en la DB.
                $pedidoCreado->save();

                //-------------------- CREACION DE LAS VENTAS DEL PEDIDO POR CADA PRODUCTO PEDIDO ------------------//

                $this->crear_y_GuardarVentasPorCadaProducto($productosPedidos, $pedidoCreado);

                $payload = json_encode(array("mensajeFinal" => "Pedido creado con exito.",
                "exito" => "exitoso","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => $pedidoCreado->id,"idVenta" => null));
            }
            else
            {
                if ($mesaEncontrada == null)
                {
                    $payload = json_encode(array("mensajeFinal" => "El pedido no puede realizarse. No se encontro la mesa.",
                    "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
                }
            
                switch ($validacionTotalDeProductos) 
                {
                    case "-1":
                    {
                        $payload = json_encode(array("mensajeFinal" => "El pedido no puede realizarse. Hay al menos un producto invalido...",
                        "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                        "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
                        break;
                    }
                    case "-2":
                    {
                        $payload = json_encode(array("mensajeFinal" => "El pedido no puede realizarse. Hay al menos un producto insuficiente...",
                        "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                        "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
                        break;
                    }
                    case "-3":
                    {
                        $payload = json_encode(array("mensajeFinal" => "El pedido no puede realizarse. Hay al menos un producto inexistente...",
                        "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                        "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
                        break;
                    }
                }

                switch ($validacionDisponibilidadPersonal) 
                {
                    case '-1':
                    {
                        $payload = json_encode(array("mensajeFinal" => "El pedido no puede realizarse. Falta al menos un cervezero...",
                        "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                        "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
                        break;
                    }
                    case '-2':
                    {
                        $payload = json_encode(array("mensajeFinal" => "El pedido no puede realizarse. Falta al menos un bartender...",
                        "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                        "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
                        break;
                    }
                    case '-3':
                    {
                        $payload = json_encode(array("mensajeFinal" => "El pedido no puede realizarse. Falta al menos un cocinero...",
                        "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                        "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
                        break;
                    }
                }
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Pedido creado sin exito. No posee los permisos.",
            "exito" => "fallido","tipo" => "alta","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }

        //Retorno la respuesta con el body que contiene un mensaje.
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

            //Busco la ID recibida y me guardo al pedido encontrado si es que se encontró alguno.
            $pedidoEncontrado = App\Models\Pedido::find($idRecibida);
                
            if ($pedidoEncontrado != null)
            {
                //El ORM borra automaticamente el pedido en la DB.
                $pedidoEncontrado->delete();
            
                //Cancelar todos los productos de la cocina/bar/cerveceria ya que el pedido fue dado de baja.
                $this->cancelarProductosDeUnPedido($pedidoEncontrado);

                $payload = json_encode(array("mensajeFinal" => "Pedido borrado con exito",
                "exito" => "fallido","tipo" => "baja","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => $pedidoEncontrado->id,"idVenta" => null)); 
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "Pedido no eliminado. No fue posible encontrar el pedido.",
                "exito" => "fallido","tipo" => "baja","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));  
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Pedido eliminado sin exito. No posee los permisos.",
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
        // PERMISOS DE ACCION: socio y mozo
        //----------------------------------------------------------------------------------//
        if ($tipoUsuarioResponsable == "socio" || $tipoUsuarioResponsable == "mozo")
        {
            //pendiente - cancelado - entregado

            // //Recibo la ID por el "link".
            $id = $args['id'];

            //Busco en la DB en el campo 'id' el valor del ID recibido y me guardo a la mesa encontrada si es que se encontró alguna.
            $pedidoEncontrado = App\Models\Pedido::where('id', '=', $id)->first();
            
            //Leo el json "RAW" de Postman y hago el decode de los datos nuevos para modificar al usuario .
            $body = json_decode(file_get_contents("php://input"), true);

            if ($pedidoEncontrado != null)
            {
                $estadoRecibido = $body['estado'];

                if ($estadoRecibido == "cancelado" || $estadoRecibido == "pendiente" && $estadoRecibido == "entregado")
                {
                    //Piso los datos 'viejos' por los 'nuevos' datos del pedido a modificar.
                    $pedidoEncontrado->estado = $estadoRecibido;
                
                    //El ORM guarda automaticamente el pedido en la DB.
                    $pedidoEncontrado->save();

                    //Tengo que cancelar todos los productos de la cocina/bar/cerveceria ya que el pedido fue dado de baja.
                    if ($estadoRecibido == "cancelado")
                    {
                        $this->cancelarProductosDeUnPedido($pedidoEncontrado); 
                    }

                    $payload = json_encode(array("mensajeFinal" => "Pedido modificado con exito.",
                    "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => $pedidoEncontrado->id, "idVenta" => null));
                }
                else
                {
                    $payload = json_encode(array("mensajeFinal" => "Pedido modificado sin exito. El estado ingresado no es valido.",
                    "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null, "idVenta" => null));
                }
            }
            else
            {
                $payload = json_encode(array("mensajeFinal" => "Pedido modificado sin exito. No se pudo encontrar.",
                "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "Pedido modificado sin exito. No posee los permisos.",
            "exito" => "fallido","tipo" => "modificacion","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }

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
            //Me traigo a todos los pedidos.
            $listaPedidos = App\Models\Pedido::all();
        
            if (count($listaPedidos) <= 0)
            {
                $payload = json_encode(array("mensaje" => "No existe ningun pedido."));  
                
                $payload = json_encode(array("mensajeFinal" => "No existe ningun pedido.",
                "exito" => "fallido","tipo" => "listarTodos","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
            else
            {
                //Voy a devolver como texto dentro de la respuesta a los pedidos encontrados.
                $JsonPedidos = json_encode(array("listaPedidos" => $listaPedidos));

                //Imprimo todas los pedidos.
                echo($JsonPedidos);

                $payload = json_encode(array("mensajeFinal" => "Se listaron todos los pedidos.",
                "exito" => "exitoso","tipo" => "listarTodos","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null)); 
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "El listado de todas los pedidos no fue posible. No posee permisos.",
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

            //Me traigo a todos los pedidos y busco al pedido con el ID recibido.
            $listaPedidos = Pedido::all();
            $pedidoEncontrado = $listaPedidos->find($idRecibido);
            
            if ($pedidoEncontrado != null)
            {
                //Voy a devolver como texto dentro de la respuesta al pedido encontrado.
                $JsonPedido = json_encode($pedidoEncontrado);
                
                //Imprimo el pedido.
                echo($JsonPedido);

                $payload = json_encode(array("mensajeFinal" => "El listar de un pedido fue realizado.",
                "exito" => "exitoso","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => $idRecibido,"idVenta" => null));
            } 
            else
            {
                $payload = json_encode(array("mensajeFinal" => "El listar de un pedido no fue posible. No se encontro el pedido.",
                "exito" => "fallido","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "El listado de un pedido no fue posible. No posee permisos.",
            "exito" => "fallido","tipo" => "listarUno","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }
    
        //Retorno la respuesta con el body que contiene un mensaje.
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function AgregarFoto($request, $response, $args)
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
            //Recibo el body del form-data en forma de array asociativo.
            $parametros = $request->getParsedBody();
            $idPedidoRecibido = $parametros['idPedido'];

            //Me traigo a todos los pedidos y busco al pedido con el ID recibido.
            $listaPedidos = Pedido::all();
            $pedidoEncontrado = $listaPedidos->find($idPedidoRecibido);

            //Si se encontro el pedido y la foto no es nula, la muevo a mi path, agregandole al final un '-{id}'
            if ($pedidoEncontrado != null && $_FILES["fotoCliente"] != null )
            {
                //Recibo el nombre en crudo
                $nombreFotoRecibida = "fotos/".$_FILES["fotoCliente"]["name"];
                
                //Le saco la extension o todo lo que tenga despues de un "."
                $nombreFotoRecibidaSinExt = explode(".",$nombreFotoRecibida);

                //Con esa parte excluida del ".", le voy a agregar "-{id}+extension fija jpg"
                $destino = $nombreFotoRecibidaSinExt[0] . "-" . $idPedidoRecibido . ".jpg";

                //Muevo el archivo y uplodeo la DB
                move_uploaded_file($_FILES["fotoCliente"]["tmp_name"],$destino);

                $pedidoEncontrado->pathFoto = $destino;
                $pedidoEncontrado->update(['pathFoto' => $destino]);
                
                $payload = json_encode(array("mensajeFinal" => "Foto uploadeada al pedido.",
                "exito" => "fallido","tipo" => "agregarDato","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => $pedidoEncontrado->id, "idVenta" => null));   
            }
            else if ($pedidoEncontrado == null)
            {
                $payload = json_encode(array("mensajeFinal" => "No se encontro un pedido al que asignarle una foto.",
                "exito" => "fallido","tipo" => "agregarDato","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));    
            }
            else 
            {
                $payload = json_encode(array("mensajeFinal" => "No se encontro una foto para asignar a un pedido.",
                "exito" => "fallido","tipo" => "agregarDato","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));    
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "El agregado de una foto al pedido no fue posible. No posee permisos.",
            "exito" => "fallido","tipo" => "agregarDato","hora" => date('h:i:s'),"idUsuarioResponsable" => $idUsuarioResponsable, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, "idPedido" => null,"idVenta" => null));     
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // --------------- FUNCIONES PRIVADAS Y NECESARIAS PARA VALIDAR O CALCULAR -------------------------------------------//
    private function crear_y_GuardarVentasPorCadaProducto($arrayAsociativoProductosRecibido,$pedidoCreadoRecibido)
    {

        $listaProductos = App\Models\Producto::all();

        foreach ($arrayAsociativoProductosRecibido as $element) 
        {
            $ventaCreada = new Venta();
            $ventaCreada->cantidad = $element["cantidadProducto"];
            
            //Los distintos estados posibles: // pendiente - listo //
            $ventaCreada->estado = "pendiente";
            
            $idRecibido = $element["idProducto"]; 

            $productoEncontrado = $listaProductos->find($idRecibido);

            $ventaCreada->tiempoEstimado = $productoEncontrado->tiempoMinutos;
            $ventaCreada->horaPreparacion = date('h:i:s');
            $ventaCreada->idProducto = $idRecibido;
            $ventaCreada->idPedido = $pedidoCreadoRecibido->id;
            $ventaCreada->idUsuario = $this->asignarEmpleadoAProducto($productoEncontrado); 

            $this->restarStockAdquirido($ventaCreada);

            //El ORM guarda automaticamente el pedido en la DB.
            $ventaCreada->save();
        }
    }

    private function asignarEmpleadoAProducto($productoRecibido)
    {
        //Esto es lo que yo quiero obtener. Un ID RANDOM de un responsable de la correspondiente area.
        $idDelResponsable = -1;

        //Me fijo el area de la cual necesito un emplead-o
        $areaResponsable = $productoRecibido->area;

        //---------------- Me traigo todos los user y me fijo que onda, si tengo o no en cada area -----------------
        $listaUsuarios = App\Models\Usuario::all();

        $hayBartander = false;
        $hayCocinero = false;
        $hayCervezero = false;
        
        //Me fijo que empleados de que seccion tengo. Con uno como minimo me conformo.
        foreach($listaUsuarios as $usuario)
        {
            if ($usuario->tipo == "bartender")
            {
                $hayBartander = true;
            }
            else if ($usuario->tipo == "cocinero")
            {
                $hayCocinero = true;
            }
            else if ($usuario->tipo == "cervecero")
            {
                $hayCervezero = true;
            }
        }

         //---------------------------------------------------------------------------------------------------------

        //Si tengo como minimo un empleado al area que corresponda asignar,
        //voy a buscar un random de esa area 
        switch ($areaResponsable) 
        {
            case 'bar':
            {
                //Solo voy a iniciar el proceso de elegir un random de tipo bartender si hay como minimo uno.
                if ($hayBartander == true)
                {
                    //Me traigo a todos los usuarios del tipo correspondiente, los guardo en un array
                    //los cuento y lo que me -1 lo puedo tratar como posicion "max". Entre la pos max y min
                    //voy a elegir un responsable random.
                    $usuario = new App\Models\Usuario();
                    $arrayBartenders = $usuario->where('tipo','bartender')->get();

                    $cantidadBartenders = count($arrayBartenders);
                    $posicionMaxArrayBartenders = $cantidadBartenders - 1;
                
                    $posBartenderElegido = rand(0,$posicionMaxArrayBartenders);
                    $idDelResponsable = $arrayBartenders[$posBartenderElegido]->id;
                }

                break;
            }
            case 'cocina':
            {
                //Solo voy a iniciar el proceso de elegir un random de tipo cocinero si hay como minimo uno.
                if ($hayBartander == true)
                {
                    //Me traigo a todos los usuarios del tipo correspondiente, los guardo en un array
                    //los cuento y lo que me -1 lo puedo tratar como posicion "max". Entre la pos max y min
                    //voy a elegir un responsable random.
                    $usuario = new App\Models\Usuario();
                    $arrayCocineros = $usuario->where('tipo','cocinero')->get();

                    $cantidadCocineros = count($arrayCocineros);
                    $posicionMaxArrayCocineros = $cantidadCocineros - 1;
                
                    $posCocineroElegido = rand(0,$posicionMaxArrayCocineros);
                    $idDelResponsable = $arrayCocineros[$posCocineroElegido]->id;
                }

                break;
            }
            case 'cerveceria':
            {
                if ($hayCervezero == true)
                {
                    //Me traigo a todos los usuarios del tipo correspondiente, los guardo en un array
                    //los cuento y lo que me -1 lo puedo tratar como posicion "max". Entre la pos max y min
                    //voy a elegir un responsable random.
                    $usuario = new App\Models\Usuario();
                    $arrayCocineros = $usuario->where('tipo','cervecero')->get();

                    $cantidadCocineros = count($arrayCocineros);
                    $posicionMaxArrayCocineros = $cantidadCocineros - 1;
                
                    $posCocineroElegido = rand(0,$posicionMaxArrayCocineros);
                    $idDelResponsable = $arrayCocineros[$posCocineroElegido]->id;
                }

                break;
            }
        }

        return $idDelResponsable;
    }

    private function validacionDisponibilidadPersonal($arrayAsociativoProductosRecibido)
    {
        $hayDisponibilidad = 0;

        //Me fijo si lo que recibi es minimamente valido
        if ($arrayAsociativoProductosRecibido != null && count($arrayAsociativoProductosRecibido) > 0)
        {
            $listaProductos = App\Models\Producto::all();
            $listaUsuarios = App\Models\Usuario::all();

            $hayBartander = false;
            $hayCocinero = false;
            $hayCervezero = false;
            
            //Me fijo que empleados de que seccion tengo. Con uno como minimo me conformo.
            foreach($listaUsuarios as $usuario)
            {
                if ($usuario->tipo == "bartender")
                {
                    $hayBartander = true;
                }
                else if ($usuario->tipo == "cocinero")
                {
                    $hayCocinero = true;
                }
                else if ($usuario->tipo == "cervecero")
                {
                    $hayCervezero = true;
                }
            }

            //Me voy a fijar POR CADA PRODUCTO, si existe su correspondiente responsable de area.
            foreach ($arrayAsociativoProductosRecibido as $element) 
            {
                //Busco al producto con el id recibido
                $idRecibido = $element["idProducto"]; 
                $productoEncontrado = $listaProductos->find($idRecibido);
                
                if ($productoEncontrado != null)
                {   
                    if($productoEncontrado->area == "cerveceria" && $hayCervezero == false)
                    {
                        //Falta un cervecero como minimo...
                        $hayDisponibilidad = -1;
                        return $hayDisponibilidad;
                    }
                    else if ($productoEncontrado->area == "bar" && $hayBartander == false)
                    {
                        //Falta un bartender como minimo...
                        $hayDisponibilidad = -2;
                        return $hayDisponibilidad;
                    }
                    else if ($productoEncontrado->area == "cocina" && $hayCocinero == false)
                    {
                        //Falta un cocinero como minimo...
                        $hayDisponibilidad = -3;
                        return $hayDisponibilidad;
                    }
                }
            }
        }

        return $hayDisponibilidad;
    }

    private function validarTotalmenteProductosPedidos($arrayAsociativoProductosRecibido)
    {
        //En un principio son productos validos
        $sonProductosValidos = 0;

        //Me traigo la lista entera de productos cargados a la DB.
        $listaProductos = App\Models\Producto::all();

        foreach($arrayAsociativoProductosRecibido as $elemento)
        {
            //Por cada producto en el array asociativo voy a acceder y guardame el ID
            // y a la cantidad que se solicita del producto
            $idRecibido = $elemento["idProducto"]; 
            $cantidadSolicitada = $elemento["cantidadProducto"];

            if($idRecibido != null && is_nan($idRecibido) == false && $cantidadSolicitada != null && is_nan($cantidadSolicitada) == false)
            {
                //Valido la existencia del producto y si la cantidad que posee es suficiente para adquirirse.
                $productoEncontrado = $listaProductos->find($idRecibido);
            
                if ($productoEncontrado != null)
                {
                    //Comparo la cantidad disponible en stock del producto, con la solicitada
                    $cantidadDisponible = $productoEncontrado->stock;

                    if ($cantidadDisponible < $cantidadSolicitada)
                    {
                        //El pedido no puede realizarse. Hay al menos un producto insuficiente...
                        $sonProductosValidos = -2;
                        return $sonProductosValidos;
                    }
                }
                else
                {
                    //El pedido no puede realizarse. Hay al menos un producto inexistente...
                    $sonProductosValidos = -3;
                    return $sonProductosValidos;
                }
            }
            else
            {
                //El pedido no puede realizarse. Hay al menos un producto invalido...
                $sonProductosValidos = -1;     
            }
        }

        return $sonProductosValidos;
    }
    
    private function calcularPrecioTotal($productos)
    {
        $acumulador = 0;

        $listaProductos = App\Models\Producto::all();
        $productoEncontrado;

        foreach($productos as $element)
        {
            $idRecibido = $element["idProducto"]; 
            $productoEncontrado = $listaProductos->find($idRecibido);
            $precioTotalSingular = $productoEncontrado->precio * $element["cantidadProducto"];

            $acumulador = $acumulador + $precioTotalSingular;
        }

        return $acumulador;
    }

    private function calcularTiempoEstimado($productos)
    {
        $tiempoMaximoEstimado;
        $contador = 0;

        $listaProductos = App\Models\Producto::all();
        $productoEncontrado;

        foreach($productos as $element)
        {
            $idRecibido = $element["idProducto"];
            $productoEncontrado = $listaProductos->find($idRecibido);

            if ($contador == 0 || $productoEncontrado->tiempoMinutos > $tiempoMaximoEstimado)
            {
                //Hay un nuevo tiempo maximo mayor.
                $tiempoMaximoEstimado = $productoEncontrado->tiempoMinutos;
            }

            $contador++;
        }

        return $tiempoMaximoEstimado;
    }

    private function restarStockAdquirido($ventaCreadaRecibida)
    {
        //Me guardo el id del producto y la cantidad adquirida, y al encontrarlo en la db, resto 
        //la cantidad adquirida
        $idProductoAdquirido = $ventaCreadaRecibida->idProducto;
        $cantidadAdquirida = $ventaCreadaRecibida->cantidad;

        $listaProductos = App\Models\Producto::all();
        $productoEncontrado = $listaProductos->find($idProductoAdquirido);

        $productoEncontrado->stock = $productoEncontrado->stock - $cantidadAdquirida;
        $productoEncontrado->save();

    }

    private function cancelarProductosDeUnPedido($pedidoRecibido)
    {
        $listaVentas = Venta::all();

        foreach($listaVentas as $venta)
        {
            //Si encuentro un pedido que sea dueño de un producto de la lista ventas
            if ($venta->idPedido == $pedidoRecibido->id)
            {
                $venta->estado = "cancelado";
                $venta->save();
            }
        }
    }
    //----------------------------------------------------------------------------------------------------------------------//
}
