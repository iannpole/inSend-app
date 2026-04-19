<?php

namespace App\Models;

use Laravel\Sanctum\Contracts\HasAbilities;
use MongoDB\Laravel\Eloquent\Model;

/**
 * MongoDB-backed PersonalAccessToken for Laravel Sanctum.
 *
 * Sanctum's default token model extends Illuminate\Database\Eloquent\Model
 * (SQL/PDO). Because this app uses MongoDB exclusively we extend
 * MongoDB\Laravel\Eloquent\Model and re-implement the Sanctum interface.
 */
class PersonalAccessToken extends Model implements HasAbilities
{
    protected $connection = 'mongodb';
    protected $collection = 'personal_access_tokens';

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'tokenable_id',
        'tokenable_type',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'abilities'    => 'json',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /** Polymorphic relation back to the User (or any tokenable model). */
    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }

    /**
     * Find the token instance matching the given raw token string.
     * Sanctum tokens are stored as sha256 hashes.
     */
    public static function findToken(string $token): ?static
    {
        if (strpos($token, '|') === false) {
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $plainToken] = explode('|', $token, 2);

        if ($instance = static::find($id)) {
            return hash_equals($instance->token, hash('sha256', $plainToken))
                ? $instance
                : null;
        }

        return null;
    }

    /** @inheritdoc */
    public function can($ability): bool
    {
        return in_array('*', $this->abilities)
            || array_key_exists($ability, array_flip($this->abilities));
    }

    /** @inheritdoc */
    public function cant($ability): bool
    {
        return ! $this->can($ability);
    }
}
