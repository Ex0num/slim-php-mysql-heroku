<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    //Establezco la 'configuracion' de la tabla perteneciente a esta clase.
    protected $primaryKey = 'id';
    protected $table = 'ventas';
    public $incremeting = true;
    public $timestamps = true;

    //Redefino el nombre de mis columnas "CREATED_AT, DELETED_AT, UPDATED_AT".
    const CREATED_AT = 'fechaAlta';
    const DELETED_AT = 'fechaBaja';
    const UPDATED_AT = 'fechaModificacion';

    //Una venta TIENE UN pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class,'idPedido');
    }

    //Una venta TIENE UN producto
    public function producto()
    {
        return $this->hasOne(Producto::class,'idProducto');
    }

    //Una venta TIENE UN usuario
    public function usuario()
    {
        return $this->hasOne(Usuario::class,'idUsuario');
    }

    //Las columnas de mi tabla.
    public $fillable = [
        'cantidad','estado','horaPreparacion',
        'horaFinalizacion','tiempoEstimado','idProducto','idPedido','idUsuario',
        'fechaAlta','fechaBaja','fechaModificacion'
    ]; 
}


?>