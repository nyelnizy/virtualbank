<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RefreshToken
 * @package App\Models\V1
 * @property integer $user_id
 * @property string $token
 * @property string $expires
 * @property string $access_token_expires
 * @property boolean $revoked
 */
class RefreshToken extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'token',
        'expires',
        'access_token_expires',
        'invalidated'
    ];
    protected $casts=[
        'expires'=>'datetime:Y-m-d H:s',
        'access_token_expires'=>'datetime:Y-m-d H:s'
    ];
}
