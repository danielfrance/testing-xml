<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
    ];

    public function filings()
    {
        return $this->hasMany(Filing::class);
    }
}
