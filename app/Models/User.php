<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // User.php
    protected $fillable = [
        'name', 'email', 'password', 'role', 'status', 'employee_id'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_STAFF = 'staff';

    // Status constants  
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // Helper methods
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff()
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getAvatarUrl()
    {
        $hash = md5(strtolower(trim($this->email)));
        
        // Options for 'd' parameter:
        // - identicon: geometric pattern based on hash
        // - monsterid: monster icon
        // - wavatar: generated faces
        // - retro: 8-bit arcade style
        // - robohash: robot avatars
        
        return "https://www.gravatar.com/avatar/{$hash}?d=monsterid&s=80";
    }
}
