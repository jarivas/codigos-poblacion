<?php

declare(strict_types=1);

namespace CodigosPoblacion\Tests;

use PHPUnit\Framework\TestCase;
use CodigosPoblacion\SearchMunicipio;

class SearchMunicipioTest extends TestCase
{
    public function test_search(): void
    {
        $search = new SearchMunicipio();

        $result = $search->search('31','arc');

        $this->assertIsArray($result);

        $this->assertSame(2, count($result));

        $data = $result[0]->toArray();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('codigo', $data);
        $this->assertArrayHasKey('provincia', $data);
        $this->assertArrayHasKey('nombre', $data);
        $this->assertArrayHasKey('fullText', $data);
    }

    public function test_get_provincias(): void
    {
        $search = new SearchMunicipio();

        $result = $search->getProvincias();

        $this->assertIsArray($result);

        $this->assertCount(52, $result);

        $data = $result[0]->toArray();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('nombre', $data);
        $this->assertArrayHasKey('fullText', $data);
    }
}