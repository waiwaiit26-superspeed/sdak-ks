<?php
namespace App\Models;

use App\Core\Model;

/**
 * AuthTokenModel — manages `auth_tokens` table
 */
class AuthTokenModel extends Model
{
    protected string $table = 'auth_tokens';
}
