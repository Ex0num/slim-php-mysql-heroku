<?php

class Producto
{
    public $nombre;
    public $precio;
    public $tipo;
    public $stock;
    public $id;

    public function crearProducto()
    {
        //Obtengo la instancia del 'accesoDatos' de mi SQL.
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        //Prepraro y me guardo la consulta INSERT del nuevo Producto que se pretende dar de alta.
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, precio, tipo, stock) VALUES (:nombre, :precio, :tipo, :stock)");

        //Bindeo los values
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $this->stock);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        $consulta = $objAccesoDatos->prepararConsulta("SELECT nombre, precio, tipo, stock, id FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }
}
?>