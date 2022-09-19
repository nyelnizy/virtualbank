<?php
namespace App\Repositories\V1;

use App\Models\V1\RefreshToken;

class AuthRepository
{
  public function createToken(array $input) : RefreshToken{
      return RefreshToken::create($input);
  }
  public function getRefreshToken(string $token): ?RefreshToken{
      return RefreshToken::where('token', $token)
          ->whereDate('expires', '>=', (string)now())
          ->where('invalidated', false)
          ->first();
  }

  public function getLatestToken(int $user_id): ?RefreshToken{
     return RefreshToken::where('user_id', $user_id)
          ->orderBy('created_at', 'desc')->first();
  }
}
