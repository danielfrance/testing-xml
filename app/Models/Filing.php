<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Filing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'filing_type_id',
        'status',
        'prepared_date',
        'team_id',
    ];

    protected $dates = [
        'prepared_date',
    ];

    protected $appends = [
        'filing_type_name',
    ];

    public function scopeTeamOwned($query, $teamId, $filingId)
    {
        return $query->where('team_id', $teamId)->findOrFail($filingId);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function companyInfo()
    {
        return $this->hasOne(CompanyInfo::class);
    }

    public function companyApplicants()
    {
        return $this->belongsToMany(CompanyApplicant::class);
    }

    public function beneficialOwners()
    {
        return $this->belongsToMany(BeneficialOwner::class);
    }

    public function filingType()
    {
        return $this->belongsTo(FilingType::class);
    }

    public function taxIDType()
    {
        return $this->belongsTo(TaxIDType::class, 'tax_id_type_id');
    }

    public function getFilingTypeNameAttribute()
    {
        return $this->filingType ? $this->filingType->name : null;
    }

    public function getBeneficialOwnerFiles()
    {
        // Eager load the BeneficialOwners with their files
        $owners = $this->beneficialOwners()->with('files')->get();

        // Collect all files, filter out any null values in case some owners don't have files
        $files = $owners->flatMap(function ($owner) {
            return $owner->file ? [$owner->file] : [];
        });

        return $files;
    }

    public function getCompanyApplicantFiles()
    {
        // Eager load the CompanyApplicants with their files
        $applicants = $this->companyApplicants()->with('files')->get();

        // Collect all files, filter out any null values in case some applicants don't have files
        $files = $applicants->flatMap(function ($applicant) {
            return $applicant->file ? [$applicant->file] : [];
        });

        return $files;
    }
}
