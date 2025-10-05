<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ProductsImportPreview implements ToArray
{
    protected $rows = [];

    public function array(array $array)
    {
        // Bỏ row header nếu cần
        $this->rows = array_slice($array, 1);
    }

    public function getRows()
    {
        $result = [];
        foreach ($this->rows as $row) {
            $result[] = [
                'name' => $row[0] ?? null,
                'description' => $row[1] ?? null,
                'price' => $row[2] ?? null,
                'category_id' => $row[3] ?? null,
                'stock' => $row[4] ?? null,
                'commission_rate' => $row[5] ?? null,
            ];
        }
        return $result;
    }
}
