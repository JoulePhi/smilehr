<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Scopes\EmployeeScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'branch_office_id',
        'department_id',
        'position_id',
        'cost_center_id',
        'nik',
        'profile_url',
        'phone',
        'birth_date',
        'birth_place',
        'gender',
        'religion',
        'phone_number',
        'address',
        'domicile_address',
        'last_education',
        'join_date',
        'employment_status',
        'contract_start',
        'contract_end',
        'late_deduction',
        'late_tolerance',
        'allow_remote_attendance',
        'allow_branch_hopping',
        'id_card_number',
        'check_in_mode',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'late_deduction' => 'float',
            'late_tolerance' => 'float',
            'allow_remote_attendance' => 'boolean',
            'allow_branch_hopping' => 'boolean',
        ];
    }



    public function financial(): HasOne
    {
        return $this->hasOne(UserFinancial::class, 'user_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchOffice::class, 'branch_office_id', 'id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id', 'id');
    }

    /**
     * Get all of the schedules for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'user_id', 'id');
    }

    #[Scope]
    protected function employee(Builder $query): void
    {
        $query->whereHas('roles', function ($query) {
            $query->where('name', 'Employee');
        });
    }
}
