<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encuesta extends Model
{
    use SoftDeletes;

    //Establezco la 'configuracion' de la tabla perteneciente a esta clase.
    protected $primaryKey = 'id';
    protected $table = 'encuestas';
    public $incremeting = true;
    public $timestamps = true;

    //Redefino el nombre de mis columnas "CREATED_AT, DELETED_AT, UPDATED_AT".
    const CREATED_AT = 'fechaAlta';
    const DELETED_AT = 'fechaBaja';
    const UPDATED_AT = 'fechaModificacion';

    //Foreings keys.
    public function pedido()
    {
        return $this->belongsTo(Pedido::class,'idPedido');
    }

    //Las columnas de mi tabla.
    public $fillable = [
        'idPedido','opinion','valoracion',
        'hora','fechaAlta','fechaModificacion','fechaBaja'
    ]; 
}
?>