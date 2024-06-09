<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_info';

    protected $fillable = [
        'team_id',
        'get_fincen',
        'foreign_pooled_investment',
        'existing_reporting_company',
        'filing_id',
        'legal_name',
        'alternate_name',
        'tax_id_type_id',
        'tax_id_number',
        'tax_id_country_id',
        'formation_type',
        'country_formation_id',
        'state_formation_id',
        'tribal_formation_id',
        'tribal_other_name',
        'current_street_address',
        'current_city',
        'current_state_id',
        'current_country_id',
        'zip',
    ];



    protected $appends = [
        'current_country_name',
        'current_country_code',
        'current_state_name',
        'current_state_code',
        'current_tribe_name',
        'country_formation_name',
        'country_formation_code',
        'state_formation_name',
        'state_formation_code',
        'tribal_formation_name',
        'tax_id_type_name',
        'tax_id_country_name',
        'tax_id_country_code',
    ];

    public function scopeTeamOwned($query, $teamId, $filingId)
    {
        return $query->where('team_id', $teamId)->findOrFail($filingId);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function filing()
    {
        return $this->belongsTo(Filing::class);
    }

    public function getCurrentCountryNameAttribute()
    {
        if ($this->current_country_id) {
            return Country::find($this->current_country_id)->name;
        }
    }

    public function getCurrentCountryCodeAttribute()
    {
        if ($this->current_country_id) {
            return Country::find($this->current_country_id)->iso;
        }
    }

    public function getCurrentStateNameAttribute()
    {
        if ($this->current_state_id) {
            return State::find($this->current_state_id)->name;
        }
    }

    public function getCurrentStateCodeAttribute()
    {
        if ($this->current_state_id) {
            return State::find($this->current_state_id)->abbreviation;
        }
    }

    public function getCurrentTribeNameAttribute()
    {
        if ($this->current_tribe_id) {
            return Tribe::find($this->current_tribe_id)->name;
        }
    }

    public function getCountryFormationNameAttribute()
    {
        if ($this->country_formation_id) {
            return Country::find($this->country_formation_id)->name;
        }
    }

    public function getCountryFormationCodeAttribute()
    {
        if ($this->country_formation_id) {
            return Country::find($this->country_formation_id)->iso;
        }
    }

    public function getStateFormationNameAttribute()
    {
        if ($this->state_formation_id) {
            return State::find($this->state_formation_id)->name;
        }
    }

    public function getStateFormationCodeAttribute()
    {
        if ($this->state_formation_id) {
            return State::find($this->state_formation_id)->abbreviation;
        }
    }

    public function getTribalFormationNameAttribute()
    {
        if ($this->tribal_formation_id) {
            return Tribe::find($this->tribal_formation_id)->name;
        }
    }

    public function getTaxIDTypeNameAttribute()
    {
        if ($this->tax_id_type_id) {
            return TaxIDType::find($this->tax_id_type_id)->name;
        }
    }

    public function getTaxIDCountryNameAttribute()
    {
        if ($this->tax_id_country_id) {
            return Country::find($this->tax_id_country_id)->name;
        }
    }

    public function getTaxIDCountryCodeAttribute()
    {
        if ($this->tax_id_country_id) {
            return Country::find($this->tax_id_country_id)->iso;
        }
    }
}
