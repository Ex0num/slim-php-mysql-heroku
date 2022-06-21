<?php

require_once './models/Usuario.php';
use \App\Models\Usuario as Usuario;

class Validaciones
{
    //============================================ VALIDACIONES TEXTUALES Y NUMERICAS =================================================//

    //------------------------------------------------------ CAMPOS USUARIO -----------------------------------------------------------//
    
    public static function validarUser_Usuario($userRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        //----------------------------------//
        if ($userRecibido != null && is_string($userRecibido) == true && count_chars($userRecibido) > 1)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarClave_Usuario($claveRecibida)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un STRING
        //----------------------------------//
        if ($claveRecibida != null && is_string($claveRecibida) == true)
        {
            if ($claveRecibida == null)
            {
                echo"<br> AA <br>";
            }

            if (is_string($claveRecibida) == false)
            {
                echo"<br> BB <br>";
            }

            $esValido = true;
        }

        return $esValido;
    }

    public static function validarNombre_Usuario($nombreRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        //----------------------------------//
        if ($nombreRecibido != null && is_string($nombreRecibido) == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarApellido_Usuario($apellidoRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un STRING
        //----------------------------------//
        if ($apellidoRecibido != null && is_string($apellidoRecibido) == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarEdad_Usuario($edadRecibida)
    {
        $esValida = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un numero valido
        // Este entre 0 y 199
        //----------------------------------//
        if ($edadRecibida != null && is_nan($edadRecibida) == false && $edadRecibida > 0 && $edadRecibida < 199)
        {
            $esValida = true;
        }

        return $esValida;
    }

    public static function validarEstado_Usuario($estadoRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        // IMPORTANTE: solo puede ser "activo" "suspendido"
        //----------------------------------//
        if ($estadoRecibido != null && is_string($estadoRecibido) == true && 
        ($estadoRecibido == "activo" || $estadoRecibido == "suspendido") == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarTipo_Usuario($tipoRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        // IMPORTANTE: solo puede ser "mozo", "socio", "bartender", "cervecero", "cocinero"
        //----------------------------------//
        if ($tipoRecibido != null && is_string($tipoRecibido) == true && ($tipoRecibido == "mozo" || $tipoRecibido == "socio" || 
        $tipoRecibido == "bartender" || $tipoRecibido == "cervecero" || $tipoRecibido == "cocinero") == true)
        {
            $esValido = true;
        }

        return $esValido;
    }
    //------------------------------------------------------ CAMPOS MESA -----------------------------------------------------------//

    public static function validarNumero_Mesa($numeroRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un numero valido
        // Este entre 0 y 199
        //----------------------------------//
        if ($numeroRecibido != null && is_nan($numeroRecibido) == false && $numeroRecibido > 0 && $numeroRecibido < 199)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarEstado_Mesa($estadoRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        // IMPORTANTE: solo puede ser "cerrada", "comiendo", "pagando", "esperando",
        //----------------------------------//
        if ($estadoRecibido != null && is_string($estadoRecibido) == true && 
        ($estadoRecibido == "cerrada" || $estadoRecibido == "comiendo" || $estadoRecibido == "pagando" || $estadoRecibido == "esperando") == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarDescripcion_Mesa($descripcionRecibida)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un STRING
        //----------------------------------//
        if ($descripcionRecibida != null && is_string($descripcionRecibida) == true)
        {
            $esValido = true;
        }

        return $esValido;
    }
    //------------------------------------------------------ CAMPOS PRODUCTO -----------------------------------------------------------//

    public static function validarNombre_Producto($nombreRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un STRING
        //----------------------------------//
        if ($nombreRecibido != null && is_string($nombreRecibido) == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarPrecio_Producto($precioRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un numero valido
        // Este entre 0 y 999.999
        //----------------------------------//
        if ($precioRecibido != null && is_nan($precioRecibido) == false && $precioRecibido > 0 && $precioRecibido < 999999)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarTiempo_Producto($tiempoRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un STRING
        //----------------------------------//
        if ($tiempoRecibido != null && is_string($tiempoRecibido) == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarArea_Producto($areaRecibida)
    {
        $esValida = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        // IMPORTANTE: solo puede ser "bar", "cocina", "cerveceria", "esperando",
        //----------------------------------//
        if ($areaRecibida != null && is_string($areaRecibida) == true && 
        ($areaRecibida == "bar" || $areaRecibida == "cocina" || $areaRecibida == "cerveceria" || $areaRecibida == "esperando") == true)
        {
            $esValida = true;
        }

        return $esValida;
    }

    public static function validarTipo_Producto($tipoRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        // IMPORTANTE: solo puede ser "bebida", "comida"
        //----------------------------------//
        if ($tipoRecibido != null && is_string($tipoRecibido) == true && 
        ($tipoRecibido == "bebida" || $tipoRecibido == "comida") == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarStock_Producto($stockRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un numero valido
        // Este entre 0 y 999.999
        //----------------------------------//
        if ($stockRecibido != null && is_nan($stockRecibido) == false && $stockRecibido > 0 && $stockRecibido < 999999)
        {
            $esValido = true;
        }

        return $esValido;
    }

    //------------------------------------------------------ CAMPOS PEDIDO -----------------------------------------------------------//

    public static function validarNombreCliente_Pedido($nombreRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        //----------------------------------//
        if ($nombreRecibido != null && is_string($nombreRecibido) == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarProductosPedidos_Pedido($arrayProductosPedidos)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Tenga al menos 1 producto
        //----------------------------------//
        if ($arrayProductosPedidos != null && count($arrayProductosPedidos) > 0)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarIDMesa_Pedido($idRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un numero valido
        // Este arriba de 0
        //----------------------------------//
        if ($idRecibido != null && is_nan($idRecibido) == false && $idRecibido > 0)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarEstado_Pedido($estadoRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        // IMPORTANTE: solo puede ser "cancelado", "pendiente", "entregado"
        //----------------------------------//
        if ($estadoRecibido != null && is_string($estadoRecibido) == true && 
        ($estadoRecibido == "cancelado" || $estadoRecibido == "pendiente" || $estadoRecibido == "entregado") == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    //------------- AGREGAR FOTO -------------//

    public static function validarIDPedido_Pedido($idRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un numero valido
        // Este arriba de 0
        //----------------------------------//
        if ($idRecibido != null && is_nan($idRecibido) == false && $idRecibido > 0)
        {
            $esValido = true;
        }

        return $esValido;
    }

    //---------- AGREGAR ENCUESTA ------------//

    public static function validarOpinion_Pedido($estadoRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        //----------------------------------//
        if ($estadoRecibido != null && is_string($estadoRecibido) == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarValoracion_Pedido($valoracionRecibida)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nula
        // Sea un numero valido
        // Este entre 0 y 999.999
        //----------------------------------//
        if ($valoracionRecibida != null && is_nan($valoracionRecibida) == false && $valoracionRecibida >= 1 && $valoracionRecibida <= 10)
        {
            $esValido = true;
        }

        return $esValido;
    }

    // ------------- VENTA -----------//
    public static function validarIDVenta_Pedido($idRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un numero valido
        // Este arriba de 0
        //----------------------------------//
        if ($idRecibido != null && is_nan($idRecibido) == false && $idRecibido > 0)
        {
            $esValido = true;
        }

        return $esValido;
    }

    public static function validarEstadoVenta_Pedido($estadoRecibido)
    {
        $esValido = false;

        //---------- Condiciones ----------//
        // No sea nulo
        // Sea un STRING
        //----------------------------------//
        if ($estadoRecibido != null && is_string($estadoRecibido) == true &&
        ($estadoRecibido == "pendiente" || $estadoRecibido == "listo" || $estadoRecibido == "preparandose" || $estadoRecibido == "cancelado") == true)
        {
            $esValido = true;
        }

        return $esValido;
    }

    //------------------------------------------------------------------------------------------------------------------------------//

}
?>