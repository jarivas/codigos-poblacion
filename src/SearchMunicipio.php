<?php

declare(strict_types=1);

namespace CodigosPoblacion;

use CodigosPoblacion\Models\Database\Provincia;
use CodigosPoblacion\Models\Database\Municipio;

class SearchMunicipio
{
    /**
     * It requires $query length would be longer than 3, search on provincias name and municipio name
     * @param string $provinciaId
     * @param string $query
     * @return Municipio[]|null
     */
    public static function search(string $provinciaId, string $query, int $offset = 0, int $limit = 100): ?array
    {
        if (strlen($query) < 3) {
            return null;
        }

        $model = new Municipio();

        $model->where( 'provincia', '=', $provinciaId);
        $model->where('fullText', 'LIKE', "$query%");
        $model->offset($offset);
        $model->limit($limit);

        return $model->query();
    }

    /**
     * Returns an array of Provincia
     * @return Provincia[]|null
     */
    public static function getProvincias(): array
    {
        return Provincia::get();
    }
}