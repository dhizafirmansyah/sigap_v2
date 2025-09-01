<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class KemasReaderService
{
    public function processKemasData($path)
    {
        try {
            if (!file_exists($path)) {
                throw new \Exception("File tidak ditemukan: $path");
            }
            
            Log::info('Starting Kemas Excel processing', ['path' => $path, 'file_size' => filesize($path)]);
            
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = [];
            
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            
            Log::info('Excel file info', [
                'highest_row' => $highestRow,
                'highest_column' => $highestColumn,
                'sheet_title' => $sheet->getTitle()
            ]);
            
            // Coba deteksi baris header dengan mencari kata kunci kemas
            $headerRow = $this->findHeaderRow($sheet, $highestRow);
            $dataStartRow = $headerRow + 1;
            
            Log::info('Header detection', [
                'header_row' => $headerRow,
                'data_start_row' => $dataStartRow
            ]);
            
            // Log sample data dari beberapa baris
            for ($row = 1; $row <= min(5, $highestRow); $row++) {
                $sampleData = [];
                for ($col = 'A'; $col <= 'O'; $col++) {
                    $cellValue = $sheet->getCell($col . $row)->getValueString();
                    if (!empty($cellValue)) {
                        $sampleData[$col] = $cellValue;
                    }
                }
                if (!empty($sampleData)) {
                    Log::info("Row $row content", $sampleData);
                }
            }
            
            // Proses data mulai dari baris setelah header
            for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                $brand = trim($sheet->getCell('A' . $row)->getValueString());
                $lokasi = trim($sheet->getCell('B' . $row)->getValueString());
                $cell = trim($sheet->getCell('C' . $row)->getValueString());
                
                // Skip jika baris kosong (cek kolom utama)
                if (empty($brand) && empty($lokasi) && empty($cell)) {
                    continue;
                }
                
                $rowData = [
                    'brand' => $brand ?: 'N/A',
                    'lokasi' => $lokasi ?: 'N/A',
                    'cell' => $cell ?: 'N/A',
                    'no_id' => trim($sheet->getCell('D' . $row)->getValueString()) ?: 'N/A',
                    'set' => trim($sheet->getCell('E' . $row)->getValueString()) ?: 'N/A',
                    'fw_scratched' => $this->parseQualityValue($sheet->getCell('F' . $row)->getValueString()),
                    'fw_tear' => $this->parseQualityValue($sheet->getCell('G' . $row)->getValueString()),
                    'fw_smeared' => $this->parseQualityValue($sheet->getCell('H' . $row)->getValueString()),
                    'fw_seam_open' => $this->parseQualityValue($sheet->getCell('I' . $row)->getValueString()),
                    'fw_alignment' => $this->parseQualityValue($sheet->getCell('J' . $row)->getValueString()),
                    'fw_improper_fold' => $this->parseQualityValue($sheet->getCell('K' . $row)->getValueString()),
                    'fw_wrinkled' => $this->parseQualityValue($sheet->getCell('L' . $row)->getValueString()),
                    'fw_crushed' => $this->parseQualityValue($sheet->getCell('M' . $row)->getValueString()),
                    'vfi_all' => $this->parseQualityValue($sheet->getCell('N' . $row)->getValueString()),
                ];

                $rows[] = $rowData;
                
                // Batasi untuk menghindari memory issue
                if (count($rows) >= 1000) {
                    Log::info('Reached maximum rows limit (1000), stopping processing');
                    break;
                }
            }
            
            Log::info('Kemas Excel processing completed', [
                'total_processed_rows' => count($rows),
                'first_row' => $rows[0] ?? null,
                'last_row' => end($rows) ?: null
            ]);
            
            if (empty($rows)) {
                Log::warning('No data found in Kemas Excel file');
                // Return sample data with explanation
                return [
                    [
                        'brand' => 'Info',
                        'lokasi' => 'System',
                        'cell' => 'N/A',
                        'no_id' => 'N/A',
                        'set' => 'N/A',
                        'fw_scratched' => 0,
                        'fw_tear' => 0,
                        'fw_smeared' => 0,
                        'fw_seam_open' => 0,
                        'fw_alignment' => 0,
                        'fw_improper_fold' => 0,
                        'fw_wrinkled' => 0,
                        'fw_crushed' => 0,
                        'vfi_all' => 0,
                        'error_message' => 'Tidak ada data ditemukan dalam file Excel atau format tidak sesuai'
                    ]
                ];
            }
            
            return $rows;
            
        } catch (\Exception $e) {
            Log::error('Error processing Kemas Excel file', [
                'path' => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return error data
            return [
                [
                    'brand' => 'Error',
                    'lokasi' => 'System',
                    'cell' => 'N/A',
                    'no_id' => 'N/A',
                    'set' => 'N/A',
                    'fw_scratched' => 0,
                    'fw_tear' => 0,
                    'fw_smeared' => 0,
                    'fw_seam_open' => 0,
                    'fw_alignment' => 0,
                    'fw_improper_fold' => 0,
                    'fw_wrinkled' => 0,
                    'fw_crushed' => 0,
                    'vfi_all' => 0,
                    'error_message' => 'Gagal membaca file Excel: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    private function findHeaderRow($sheet, $highestRow)
    {
        // Cari baris yang mengandung kata kunci header kemas
        $keywords = ['brand', 'lokasi', 'cell', 'no id', 'set', 'fw-scratched', 'fw-tear', 'vfi all'];
        
        for ($row = 1; $row <= min(10, $highestRow); $row++) {
            $foundKeywords = 0;
            for ($col = 'A'; $col <= 'N'; $col++) {
                $cellValue = strtolower(trim($sheet->getCell($col . $row)->getValueString()));
                foreach ($keywords as $keyword) {
                    if (strpos($cellValue, $keyword) !== false) {
                        $foundKeywords++;
                        break;
                    }
                }
            }
            
            // Jika ditemukan minimal 3 keyword, anggap sebagai header
            if ($foundKeywords >= 3) {
                Log::info("Header row detected", ['row' => $row, 'keywords_found' => $foundKeywords]);
                return $row;
            }
        }
        
        // Default ke baris 1 jika tidak ditemukan header
        Log::info("No header row detected, using row 1");
        return 1;
    }

    private function parseQualityValue($value)
    {
        if (!$value || trim($value) === '') {
            return 0;
        }

        // Clean the value
        $value = trim($value);
        $value = str_replace([',', '.00'], ['', ''], $value);

        try {
            return (int) $value;
        } catch (\Exception $e) {
            Log::warning('Failed to parse quality value', ['value' => $value]);
            return 0;
        }
    }
}
