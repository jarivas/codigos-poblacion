<?php

namespace CodigosPoblacion\Models;

use CastModels\Model;

class Municipio extends Model
{
    public string $codigo_provincia;
    public string $codigo;
    public string $codigo_control;
    public string $nombre;
    public string $fullText;
}