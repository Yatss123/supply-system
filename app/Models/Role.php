<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Role constants for easy reference.
     */
    const SUPER_ADMIN = 'super_admin';
    const ADMIN = 'admin';
    const USER = 'user';
    const STUDENT = 'student';
    const ADVISER = 'adviser';
    const DEAN = 'dean';

    /**
     * Get all available roles.
     */
    public static function getAvailableRoles()
    {
        return [
            self::SUPER_ADMIN => 'Super Administrator',
            self::ADMIN => 'Administrator',
            self::USER => 'Regular User',
            self::STUDENT => 'Student',
            self::ADVISER => 'Adviser',
            self::DEAN => 'Dean',
        ];
    }

    /**
     * Get the display name attribute.
     */
    public function getDisplayNameAttribute()
    {
        $roleNames = [
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'user' => 'Regular User',
            'student' => 'Student',
            'adviser' => 'Adviser',
            'dean' => 'Dean',
        ];

        return $roleNames[$this->name] ?? ucfirst(str_replace('_', ' ', $this->name));
    }
}
