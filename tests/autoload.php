<?php

function dd(mixed $value): void
{
    var_dump($value);
    die;
}

$rootDir = dirname(__DIR__);

require "$rootDir/vendor/autoload.php";