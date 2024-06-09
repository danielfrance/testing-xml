<?php

namespace App\Models;

use Laratrust\Models\Team as LaratrustTeam;

class Team extends LaratrustTeam
{
    public $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function filings()
    {
        return $this->hasMany(Filing::class);
    }

    public function invites()
    {
        return $this->hasMany(Invite::class);
    }

    
}
