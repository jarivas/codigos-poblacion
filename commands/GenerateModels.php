<?php

use CodigosPoblacion\Install\GenerateModels;
use CodigosPoblacion\Helpers\InstallHelper;

require 'autoload.php';

InstallHelper::setUp();
$result = GenerateModels::generate();