<?php

declare(strict_types=1);

namespace CodigosPoblacion\Models\Database;

enum Join : string
{
    case Inner = 'inner';
    case Left = 'left';
    case Right = 'right';
}//end enum
