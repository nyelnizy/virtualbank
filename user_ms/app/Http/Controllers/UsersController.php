<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Resources\VerifiedUserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    private $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function getUser(Request $request)
    {
        if ($request->username) {
            return response()->json(VerifiedUserResource::make($this->userService->getUserForLogin($request->username)));
        } else {
            return response()->json( UserResource::make($this->userService->getUser($request->id)));
        }
    }
}
