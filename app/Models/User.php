<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'temp_privilege_type',
        'temp_privilege_expires_at',
        'address',
        'date_of_birth',
        'civil_status',
        'gender',
        'contact_number',
        'department_id',
        'year_level',
        'profile_completed',
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'temp_privilege_expires_at' => 'datetime',
    ];

    /**
     * Get the role that belongs to the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($roleName)
    {
        // Add null check to prevent errors when role relationship is not loaded or user has no role
        if (!$this->role) {
            return false;
        }
        
        return $this->role->name === $roleName;
    }

    /**
     * Get the department where this user is the dean.
     */
    public function departmentAsHead()
    {
        return $this->hasOne(Department::class, 'dean_id');
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user has admin privileges (super admin or admin).
     */
    public function hasAdminPrivileges()
    {
        // Add null check to prevent errors when role relationship is not loaded
        if (!$this->role) {
            // Even if role is not loaded, check temporary privileges
            return $this->hasTemporaryAdminPrivileges();
        }
        
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasTemporaryAdminPrivileges();
    }

    /**
     * Determine if user currently has temporary admin privileges.
     */
    public function hasTemporaryAdminPrivileges(): bool
    {
        if ($this->temp_privilege_type !== 'admin') {
            return false;
        }

        // If no expiration, privilege is active until changed
        if ($this->temp_privilege_expires_at === null) {
            return true;
        }

        // Active only when expiration is in the future
        return now()->lessThan($this->temp_privilege_expires_at);
    }

    /**
     * Determine if user currently has temporary dean-level privileges.
     */
    public function hasTemporaryDeanPrivileges(): bool
    {
        if ($this->temp_privilege_type !== 'dean') {
            return false;
        }

        // If no expiration, privilege is active until changed
        if ($this->temp_privilege_expires_at === null) {
            return true;
        }

        // Active only when expiration is in the future
        return now()->lessThan($this->temp_privilege_expires_at);
    }

    /**
     * Check if user has effective dean-level privileges for a department.
     * Covers permanent dean role and temporary dean privileges scoped by user's department.
     */
    public function hasDeanPrivilegesForDepartment($departmentId): bool
    {
        $deptId = $departmentId instanceof \App\Models\Department ? $departmentId->id : $departmentId;
        if (!$deptId) {
            return false;
        }

        // Permanent dean role with matching department
        if ($this->isDean() && $this->department_id == $deptId) {
            return true;
        }

        // Temporary dean privileges also require department match
        return $this->hasTemporaryDeanPrivileges() && $this->department_id == $deptId;
    }

    /**
     * Check if user is a student.
     */
    public function isStudent()
    {
        return $this->hasRole('student');
    }

    /**
     * Check if user is an adviser.
     */
    public function isAdviser()
    {
        return $this->hasRole('adviser');
    }

    /**
     * Check if user is a dean.
     */
    public function isDean()
    {
        return $this->hasRole('dean');
    }

    /**
     * Check if user is the dean of a specific department.
     */
    public function isDeanOf($department)
    {
        // Handle both Department model instance and department ID
        $departmentId = $department instanceof Department ? $department->id : $department;
        
        return $this->isDean() && $this->department_id == $departmentId;
    }

    /**
     * Check if user has departmental privileges (adviser or dean).
     */
    public function hasDepartmentalPrivileges()
    {
        return $this->isAdviser() || $this->isDean();
    }

    /**
     * Validate that only one Dean can be assigned per department.
     */
    public function validateDeanUniqueness($departmentId, $roleId)
    {
        // Get the Dean role
        $deanRole = Role::where('name', 'dean')->first();
        
        if (!$deanRole || $roleId != $deanRole->id) {
            return true; // Not assigning Dean role, so no constraint
        }

        // Check if there's already a Dean in this department (excluding current user)
        $existingDean = User::where('department_id', $departmentId)
                           ->where('role_id', $deanRole->id)
                           ->where('id', '!=', $this->id)
                           ->first();

        return $existingDean === null;
    }

    /**
     * Get the current Dean of a department.
     */
    public static function getDeanOfDepartment($departmentId)
    {
        $deanRole = Role::where('name', 'dean')->first();
        
        if (!$deanRole) {
            return null;
        }

        return User::where('department_id', $departmentId)
                   ->where('role_id', $deanRole->id)
                   ->first();
    }

    /**
     * Get the department that belongs to the user.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the profile update requests for the user.
     */
    public function profileUpdateRequests()
    {
        return $this->hasMany(ProfileUpdateRequest::class);
    }

    /**
     * Get the pending profile update request for the user.
     */
    public function pendingProfileUpdateRequest()
    {
        return $this->profileUpdateRequests()->where('status', 'pending')->first();
    }

    /**
     * Check if user has a pending profile update request.
     */
    public function hasPendingProfileUpdate()
    {
        return $this->pendingProfileUpdateRequest() !== null;
    }

    /**
     * Get the supply requests for the user.
     */
    public function supplyRequests()
    {
        return $this->hasMany(SupplyRequest::class);
    }

    /**
     * Get the borrowed items for the user.
     */
    public function borrowedItems()
    {
        return $this->hasMany(BorrowedItem::class);
    }

    /**
     * Get the issued items for the user.
     */
    public function issuedItems()
    {
        return $this->hasMany(IssuedItem::class, 'user_id');
    }

    /**
     * Get the loan requests for the user.
     */
    public function loanRequests()
    {
        return $this->hasMany(LoanRequest::class, 'requested_by');
    }
}
