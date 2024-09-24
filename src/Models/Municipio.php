<?php

namespace CodigosPoblacion\Models;

use CastModels\Model;

class Municipio extends Model
{
    public string $codigo;
    public string $codigo_municipio;
    public string $codigo_control;
    public string $nombre;
    public string $fullText;
}