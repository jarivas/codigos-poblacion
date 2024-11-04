<?php

declare(strict_types=1);

namespace CodigosPoblacion\Tests\Install;

use PHPUnit\Framework\TestCase;
use CodigosPoblacion\Install\CsvImporter;
use CodigosPoblacion\Models\Database\Connection;

class CsvImporterTest extends TestCase
{
    public function test_import(): void
    {
        $result = CsvImporter::import();

        $this->report($result);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertGreaterThan(0, $result['count']);
        $this->assertCount(0, $result['errors']);
    }

    protected function report(array $result): void
    {
        $dir = dirname(__DIR__, 2);

        $text = json_encode($result, JSON_PRETTY_PRINT);

        file_put_contents("$dir/report.json", $text);
    }

    protected function setUp(): void
    {
        $connection = Connection::getInstance();

        $connection->exec('DELETE FROM municipio');
        $connection->exec('DELETE FROM provincia');
    }
}