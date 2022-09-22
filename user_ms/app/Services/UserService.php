<?php
namespace App\Services;

use App\Clients\BankClient;
use App\Http\Resources\UserResource;
use App\Http\Resources\VerifiedUserResource;
use App\Models\User;
use App\Repositories\UserRepository;

class UserService{
    private $userRepository;
    private $bankClient;
    public function __construct(UserRepository $userRepository,BankClient $bankClient)
    {
        $this->userRepository = $userRepository;
        $this->bankClient = $bankClient;
        
    }

    public function registerUser(array $user): User
    {
        $user = $this->userRepository->createUser($user);
        // 
        return $user;
    }

    public function getUser(int $id): User
    {
      return $this->userRepository->findUser($id);
    }
    
    public function getUserForLogin(string $name): User
    {
      return $this->userRepository->findUserByName($name);
    }

}