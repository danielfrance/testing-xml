<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxIDType extends Model
{
    use HasFactory;

    protected $table = 'tax_id_types';

    protected $fillable = [
        'name',
        'value'
    ];

    public function filings()
    {
        return $this->hasMany(Filing::class);
    }
}
