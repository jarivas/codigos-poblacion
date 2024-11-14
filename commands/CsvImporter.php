<?php

use CodigosPoblacion\Install\CsvImporter;
use CodigosPoblacion\Helpers\InstallHelper;

require 'autoload.php';

InstallHelper::setUp();

$instance = new CsvImporter();
$result = $instance->import();