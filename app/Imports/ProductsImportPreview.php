<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ProductsImportPreview implements ToArray
{
    protected $rows = [];

    public function array(array $array)
    {
        // Bỏ header
        $dataRows = array_slice($array, 1);

        foreach ($dataRows as $row) {
            $this->rows[] = [
                'name' => $row[0] ?? null,
                'description' => $row[1] ?? null,
                'price' => $row[2] ?? null,
                'stock' => $row[3] ?? null,
                'category_id' => $row[4] ?? null,
                'affiliate_link' => $row[5] ?? null,   
                'commission_rate' => $row[6] ?? 0,    
            ];
        }
    }

    public function getRows()
    {
        return $this->rows;
    }
}
