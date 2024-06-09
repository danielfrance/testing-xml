<?php

namespace App\Models;

use App\ApplicantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyApplicant extends Model
{
    use HasFactory, SoftDeletes, ApplicantTrait;

    protected $fillable = [
        'team_id',
        'fincen_id',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'dob',
        'address_type',
        'address',
        'city',
        'state_id',
        'country_id',
        'zip',
        'id_type',
        'id_number',
        'id_document_country',
        'id_document_state',
        'id_document_tribe',
        'tribal_other_name',
        'id_document_file_id',
        'email',
        'phone',
        'info_verified_at',
        'info_verified_by',
    ];

    protected $appends = [
        'file_name',
        'state_name',
        'state_code',
        'country_name',
        'country_code',
        'id_document_country_name',
        'id_document_country_code',
        'id_document_state_name',
        'id_document_state_code',
        'id_document_tribe_name',
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
        return $this->belongsToMany(Filing::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'id_document_file_id');
    }

    public function getFileNameAttribute()
    {
        return $this->file ? $this->file->name : null;
    }

    public function getStateNameAttribute()
    {
        if ($this->state_id) {
            return State::find($this->state_id)->name;
        }
    }

    public function getStateCodeAttribute()
    {
        if ($this->state_id) {
            return State::find($this->state_id)->abbreviation;
        }
    }

    public function getCountryNameAttribute()
    {
        if ($this->country_id) {
            return Country::find($this->country_id)->name;
        }
    }

    public function getCountryCodeAttribute()
    {
        if ($this->country_id) {
            return Country::find($this->country_id)->iso;
        }
    }

    public function getIdDocumentCountryNameAttribute()
    {
        if ($this->id_document_country) {
            return Country::find($this->id_document_country)->name;
        }
    }

    public function getIdDocumentCountryCodeAttribute()
    {
        if ($this->id_document_country) {
            return Country::find($this->id_document_country)->iso;
        }
    }

    public function getIdDocumentStateNameAttribute()
    {
        if ($this->id_document_state) {
            return State::find($this->id_document_state)->name;
        }
    }

    public function getIdDocumentStateCodeAttribute()
    {
        if ($this->id_document_state) {
            return State::find($this->id_document_state)->abbreviation;
        }
    }

    public function getIdDocumentTribeNameAttribute()
    {
        if ($this->id_document_tribe) {
            return Tribe::find($this->id_document_tribe)->name;
        }
    }
}
