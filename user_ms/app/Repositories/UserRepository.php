<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository{
    public function createUser(array $user): User
    {
       $user = User::create($user);
       return $user;
    }

    public function findUserByName(string $name): ?User
    {
       return User::where("name",$name)->first();
    }
    public function findUser(int $id): ?User
    {
       return User::find($id);
    }
}