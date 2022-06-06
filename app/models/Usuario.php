<?php

class Usuario
{
    public $user;
    public $clave;
    public $nombre;
    public $apellido;
    public $edad;
    public $estado;
    public $tipo;
    public $id;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (user, clave, nombre, apellido, edad, estado, tipo, fechaAlta, fechaBaja) VALUES (:user, :clave, :nombre, :apellido, :edad, :estado, :tipo, :fechaAlta, :fechaBaja)");
        
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        $fechaAlta = Date("y-m-d");
        $fechaBaja = null;

        $consulta->bindValue(':user', $this->user, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':edad', $this->edad);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
     
        $consulta->bindValue(':fechaAlta', $fechaAlta);
        $consulta->bindValue(':fechaBaja', $fechaBaja);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT user, clave, nombre, apellido, edad, estado, tipo, fechaAlta, fechaBaja, id FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    /*
    public static function obtenerUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function modificarUsuario()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET usuario = :usuario, clave = :clave WHERE id = :id");
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarUsuario($usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaBaja = :fechaBaja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $usuario, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }
    */
}