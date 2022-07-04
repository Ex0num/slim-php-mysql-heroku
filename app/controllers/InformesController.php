<?php
require_once './models/Pedido.php';
require_once './models/Venta.php';
require_once './models/Usuario.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
require_once './models/Encuesta.php';
require_once './models/Validaciones.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Venta as Venta;
use \App\Models\Mesa as Mesa;
use \App\Models\Encuesta as Encuesta;

require_once './interfaces/IApiUsable.php';
date_default_timezone_set("America/Buenos_Aires");

class InformesController
{
    public function traerInformesMesas($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//
        if ($tipoUsuarioResponsable == "socio")
        {
            //$stringMesaMasUsada = $this->MesaMasUsada();
            $stringMejorImporteMesa = $this->MejorImporteMesa();

            echo "<br>",$stringMejorImporteMesa;
        }
    }
    public function traerInformesEmpleados($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//
        if ($tipoUsuarioResponsable == "socio")
        {
            echo "<br> BIENVENIDO.";
        }
    }

    public function traerInformesPedidos($request, $response, $args)
    {
        //---------------------- TOKEN USER DATA -------------------------------------------//
        $idUsuarioResponsable = AutentificadorJWT::DevolverIdUserResponsable($request);
        $tipoUsuarioResponsable = AutentificadorJWT::DevolverTipoUserResponsable($request);
        $estadoUsuarioResponsable = AutentificadorJWT::DevolverEstadoUserResponsable($request);
        //----------------------------------------------------------------------------------//

        //------------------------USUARIOS AUTORIZADOS A REALIZAR LA ACCION-----------------//
        // PERMISOS DE ACCION: socio
        //----------------------------------------------------------------------------------//
        if ($tipoUsuarioResponsable == "socio")
        {
            echo "<br> BIENVENIDO.";
        }
    }

    //------------------------------------- FUNCIONES -------------------------------//

    public static function MejorImporteMesa()
    {
        $max = Pedido::all()->max('precioTotal');

        echo "TRAJE: ",$max;

        $pedido = Pedido::where('precioTotal', '=', $max)->first();

        if ($pedido != null) 
        {
            $string = '----------------------- MEJOR IMPORTE DE MESA ---------------------------' . '<br>' .
                'La factura de mayor costo fue $' . $pedido->precioTotal . '<br>' .
                'IdMesa: ' . $pedido->idMesa . '<br>' .
                'Codigo: ' . $pedido->codigoAlfanumerico . '<br>' .
                'Nombre Cliente: ' . $pedido->nombreCliente . '<br>';
            $corte = '-------------------------------------------------------------------------' . '<br>';
            return $string . $corte;
        }

        return "#";
    }

    public static function TotalVendido()
    {
        $max = Pedido::all()->sum('ImporteTotal');
        $string =  $max . '<br>';
        $corte = '-------------------------------------------------------------------------';
        return $string . $corte;
    }

    public static function PeorImporteMesa()
    {
        $max = Pedido::all()->min('ImporteTotal');
        $pedido = Pedido::where('ImporteTotal', '=', $max)->first();

        if ($pedido != null) {
            $string =   '---------------------------FACTURA MAS BAJA------------------------------' . '<br>' .
                'La factura de menor costo fue $' . $pedido->ImporteTotal . '<br>' .
                'IdMesa: ' . $pedido->IdMesa . '<br>' .
                'Codigo: ' . $pedido->CodigoPedido . '<br>' .
                'Nombre Cliente: ' . $pedido->NombreCliente . '<br>';
            $corte = '-------------------------------------------------------------------------' . '<br>';
            return $string . $corte;
        }
        return "#";
    }



    // public static function MejorComentario()
    // {
    //     $max = Encuesta::all()->max('valoracion');
        
    //     $pedido = Pedido::where('idPedido', '=', $max)->first();

    //     if ($pedido != null) 
    //     {  
    //        return $pedido->;
    //     }
    //     return "#";
    // }

    public static function PeorComentario()
    {
        $min = Pedido::all()->min('PuntuacionMozo');
        $pedido = Pedido::where('PuntuacionMozo', '=', $min)->first();
        if ($pedido != null) {
            $string =   '---------------------------PEOR COMENTARIO------------------------------' . '<br>' .
                'Este es un informe sobre el peor comentario del mes' . '<br>' .
                'IdPedido: ' . $pedido->IdPedido . '<br>' .
                'IdMesa: ' . $pedido->IdMesa . '<br>' .
                'Nombre Cliente: ' . $pedido->NombreCliente . '<br>' .
                'Puntuacion de mesa :'.$pedido->PuntuacionMesa . '<br>'.
                'Puntuacion de mozo :'.$pedido->PuntuacionMozo . '<br>'.
                'Puntuacion de cocinero :'.$pedido->PuntuacionCocinero . '<br>'.
                'Puntuacion de restaurante :'.$pedido->PuntuacionRestaurante . '<br>'.
                'Comentario: '.$pedido->Comentario . '<br>';
            $corte = '-------------------------------------------------------------------------' . '<br>';
           return $string . $corte;
        }
        return "#";
    }

}