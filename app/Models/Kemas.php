<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kemas extends Model
{
    protected $fillable = [
        'brand', 'lokasi', 'cell', 'no_id', 'set',
        'fw_scratched', 'fw_tear', 'fw_smeared', 'fw_seam_open',
        'fw_alignment', 'fw_improper_fold', 'fw_wrinkled', 'fw_crushed', 'vfi_all',
    ];
}
