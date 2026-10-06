<?php

namespace App\Models\Oficial\Sistema;

use App\Models\Oficial\Academico\Persona;
use App\Support\Modelos\CodigoInstitucional;
use App\Notifications\CustomResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use CodigoInstitucional;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles { hasPermissionTo as private tienePermisoPermanente; }
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $table = 'users';

    protected $primaryKey = 'cod_usu';

    public $incrementing = false;

    protected $keyType = 'string';

    protected string $guard_name = 'web';

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->tienePermisoPermanente($permission, $guardName)) return true;
        if (($guardName ?? 'web') !== 'web') return false;
        $nombre = is_string($permission) ? $permission : ($permission instanceof \Spatie\Permission\Models\Permission ? $permission->name : null);
        return $nombre !== null && app(\App\Services\AccesoProgramadoService::class)->permite($this, $nombre);
    }

    protected static function booted(): void
    {
        static::updating(function (self $usuario) {
            if ($usuario->isDirty('email') && preg_match('/^uft3\.[a-z0-9]+\.[a-z0-9]+\.[a-z0-9]{2}@gmail\.com$/i', (string) $usuario->getOriginal('email'))) {
                throw ValidationException::withMessages(['email' => 'El correo institucional de acceso es fijo y no se puede modificar.']);
            }
        });
    }

    protected $fillable = [
        'cod_usu',
        'cod_per',
        'email',
        'password',
        'email_verified_at',
        'google_id',
        'avatar',
        'auth_provider',
        'last_login_at',
        'remember_token',
        'current_team_id',
        'profile_photo_path',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'est_usu',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /** Preserva model_type de las asignaciones Spatie existentes. */
    public function getMorphClass()
    {
        return 'App\\Models\\User';
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'cod_per', 'cod_per');
    }

    public function avisos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotificacionUsuario::class, 'cod_usu', 'cod_usu');
    }

    protected function defaultProfilePhotoUrl()
    {
        return asset('image/avatar-institucional.svg');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }
}
