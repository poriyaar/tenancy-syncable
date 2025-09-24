<?php

namespace App\Models;

use App\Models\Tenant;
use App\Models\Tenant\User;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
// use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Contracts\SyncMaster;
use Stancl\Tenancy\Database\Models\TenantPivot;
use Stancl\Tenancy\Database\Concerns\ResourceSyncing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CentralUser extends Authenticatable implements SyncMaster
{
    use HasFactory, Notifiable, ResourceSyncing, CentralConnection;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_users', 'global_user_id', 'tenant_id', 'global_id')
            ->using(TenantPivot::class);
    }

    public static function bootResourceSyncing()
    {
        // این متد را خالی بگذارید تا رفتار خودکار trait غیرفعال شود.
    }

    public function getTenantModelName(): string
    {
        return User::class; // مدل داخل tenant DB
    }

    public function getCentralModelName(): string
    {
        return self::class; // خیلی مهم: اینو باید اضافه کنی
    }

    public function getGlobalIdentifierKeyName(): string
    {
        return 'global_id';
    }

    public function getGlobalIdentifierKey()
    {
        return $this->getAttribute($this->getGlobalIdentifierKeyName());
    }

    public function getSyncedAttributeNames(): array
    {
        return ['name', 'email', 'password'];
    }

     protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->global_id)) {
                $model->global_id = Str::uuid();
            }
        });
    }
}
