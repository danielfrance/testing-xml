<?php

namespace App\Models;

use App\Events\InviteCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Event;

class Invite extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'status',
        'token',
        'type',
        'team_id',
        'expires_at'
    ];

    protected $dates = [
        'expires_at'
    ];

    protected $appends = [
        'team_name'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function getTeamNameAttribute()
    {
        return $this->team->name;
    }
}
