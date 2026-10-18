<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProgressResource;
use App\Models\User;

/**
 * Mirrors Duolingo's `/users/{id}/progress`.
 */
class UserProgressController extends Controller
{
    public function __invoke(User $user): UserProgressResource
    {
        return new UserProgressResource($user);
    }
}
