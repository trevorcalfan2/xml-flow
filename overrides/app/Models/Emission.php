<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emission extends Model
{
    protected $fillable = [
        'doc_id', 'serie', 'numero', 'tipo', 'status',
        'igv', 'total', 'fecha_emision', 'url_acepta',
        'hash', 'firma', 'xml_enviado', 'respuesta_raw', 'comando',
    ];
}
