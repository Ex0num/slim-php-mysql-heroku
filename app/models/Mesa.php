<?php

class Mesa
{
    public $numero;
    public $estado;
    public $codigoAlfanumerico;
    public $id;

    public function crearMesa()
    {
        //Obtengo la instancia del 'accesoDatos' de mi SQL.
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        //Prepraro y me guardo la consulta INSERT de la nueva mesa que se pretende dar de alta.
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (numero, estado, codigoAlfanumerico) VALUES (:numero, :estado, :codigoAlfanumerico)");

        //Bindeo los values
        $consulta->bindValue(':numero', $this->numero, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue('codigoAlfanumerico', $this->codigoAlfanumerico, PDO::PARAM_STR);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        $consulta = $objAccesoDatos->prepararConsulta("SELECT numero, estado, codigoAlfanumerico, id FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

}
?>