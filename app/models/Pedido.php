<?php

class Pedido
{
    public $codigoAlfaNumerico;
    public $productosPedidos;
    public $id;

    public function crearPedido()
    {
        //Obtengo la instancia del 'accesoDatos' de mi SQL.
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        //Prepraro y me guardo la consulta INSERT del nueva pedido que se pretende dar de alta.
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigoAlfaNumerico, productosPedidos) VALUES (:codigoAlfaNumerico, :productosPedidos)");

        //Bindeo los values
        $consulta->bindValue(':codigoAlfaNumerico', $this->codigoAlfaNumerico, PDO::PARAM_STR);
        $consulta->bindValue(':productosPedidos', $this->productosPedidos);
        
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

}
?>