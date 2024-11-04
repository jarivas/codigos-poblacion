<?php

declare(strict_types=1);

namespace CodigosPoblacion\Tests\Install;

use PHPUnit\Framework\TestCase;
use CodigosPoblacion\Install\GenerateModels;
use CodigosPoblacion\Helpers\InstallHelper;

class GenerateModelsTest extends TestCase
{

    public function test_generate_models(): void
    {
        $result = GenerateModels::generate();

        $this->assertTrue($result);

        $rootdir = InstallHelper::getRootDir();
        $targetFolder = $rootdir . GenerateModels::TARGET_FOLDER;
        $testFile = "$targetFolder/Connection.php";
        
        $this->assertFileExists($testFile);

        $content = file_get_contents($testFile);
        $this->assertNotEmpty($content);
        $this->assertStringContainsString(GenerateModels::NAMESPACE, $content);
    }
}