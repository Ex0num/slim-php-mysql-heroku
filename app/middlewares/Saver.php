<?php

require_once './models/Accion.php';
use \App\Models\Accion as Accion;

class Saver
{
    public static function GuardarAccion($response)
    {
        //De la response devuelta me hago el decode del body. Ahi tengo acceso a toda la info de la accion realizada.
        $resultadoAccion = json_decode($response->getBody());
            
        //Creo la accion y le asigno los datos (recibidos de la response)
        $accionRealizada = new Accion();
        $accionRealizada->mensajeFinal = $resultadoAccion->mensajeFinal;
        $accionRealizada->exito = $resultadoAccion->exito;
        $accionRealizada->tipo = $resultadoAccion->tipo;
        $accionRealizada->hora = $resultadoAccion->hora;
        $accionRealizada->idUsuarioResponsable = $resultadoAccion->idUsuarioResponsable;
        $accionRealizada->idProducto = $resultadoAccion->idProducto;
        $accionRealizada->idMesa = $resultadoAccion->idMesa;
        $accionRealizada->idPedido = $resultadoAccion->idPedido;
        $accionRealizada->idVenta = $resultadoAccion->idVenta;

        $accionRealizada->save();
    }
}

?>