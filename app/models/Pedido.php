<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;

    //Establezco la 'configuracion' de la tabla perteneciente a esta clase.
    protected $primaryKey = 'id';
    protected $table = 'pedidos';
    public $incremeting = true;
    public $timestamps = true;

    //Redefino el nombre de mis columnas "CREATED_AT, DELETED_AT, UPDATED_AT".
    const CREATED_AT = 'fechaAlta';
    const DELETED_AT = 'fechaBaja';
    const UPDATED_AT = 'fechaModificacion';

    /*  Mis foreings.
          idMesa;
         idUsuario;
    */
    public function usuario()
    {
        return $this->hasOne(Usuario::class,'idUsuario');
    }

    public function mesa()
    {
        return $this->hasOne(Mesa::class,'idMesa');
    }


    //Las columnas de mi tabla.
    public $fillable = [
        'codigoAlfanumerico','codigoAlfaNumericoMesa','precioTotal',
        'estado','minutosEstimados','nroMesa','nombreCliente','pathFoto','idMesa','idUsuario','fechaAlta',
        'fechaBaja','fechaModificacion'
    ]; 

}
?>