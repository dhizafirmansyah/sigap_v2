<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class KemasImport implements ToCollection
{
    protected $rows = [];

    public function collection(Collection $collection)
    {
        $header = $collection->first();
        foreach ($collection->skip(1) as $row) {
            $this->rows[] = [
                'brand' => $row[0],
                'lokasi' => $row[1],
                'cell' => $row[2],
                'no_id' => $row[3],
                'set' => $row[4],
                'fw_scratched' => $row[5],
                'fw_tear' => $row[6],
                'fw_smeared' => $row[7],
                'fw_seam_open' => $row[8],
                'fw_alignment' => $row[9],
                'fw_improper_fold' => $row[10],
                'fw_wrinkled' => $row[11],
                'fw_crushed' => $row[12],
                'vfi_all' => $row[13],
            ];
        }
    }

    public function getRows()
    {
        return $this->rows;
    }
}
