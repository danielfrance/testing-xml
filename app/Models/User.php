<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    use HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'team_id',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'team_name',
        'role_name'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scopeTeamOwned($query, $teamId, $userId)
    {
        return $query->where('team_id', $teamId)->findOrFail($userId);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function getTeamNameAttribute()
    {
        return $this->team->name;
    }

    public function roles(): MorphToMany
    {
        return $this->morphToMany(Role::class, 'user', 'role_user')->where('team_id', $this->team_id);
    }

    public function getRoleNameAttribute()
    {
        return $this->roles->first()->name;
        // return $this->roles->pluck('name')->toArray();
    }

    
}
