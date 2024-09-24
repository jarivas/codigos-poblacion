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

        $result = $search->search('31','mal');
        
        $this->assertIsArray($result);
    }
}