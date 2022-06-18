<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accion extends Model
{
    use SoftDeletes;

    //Establezco la 'configuracion' de la tabla perteneciente a esta clase.
    protected $primaryKey = 'id';
    protected $table = 'acciones';
    public $incremeting = true;
    public $timestamps = true;

    //Redefino el nombre de mis columnas "CREATED_AT, DELETED_AT, UPDATED_AT".
    const CREATED_AT = 'fechaAlta';
    const DELETED_AT = 'fechaBaja';
    const UPDATED_AT = 'fechaModificacion';

    public function usuario()
    {
        return $this->belongsTo(Usuario::class,'idUsuarioResponsable');
    }

    public function usuarioAccion()
    {
        return $this->hasOne(Usuario::class,'idUsuario');
    }

    public function productoAccion()
    {
        return $this->hasOne(Producto::class,'idProducto');
    }

    public function mesaAccion()
    {
        return $this->hasOne(Mesa::class,'idMesa');
    }

    public function pedidoAccion()
    {
        return $this->hasOne(Pedido::class,'idPedido');
    }

    public function ventaAccion()
    {
        return $this->hasOne(Venta::class,'idVenta');
    }

    //Las columnas de mi tabla.
    public $fillable = [
        'mensajeFinal','exito','tipo','hora',
        'idUsuarioResponsable','idUsuario',
        'idProducto','idMesa','idPedido',
        'idVenta','fechaAlta','fechaModificacion','fechaBaja'
    ]; 
}
?>