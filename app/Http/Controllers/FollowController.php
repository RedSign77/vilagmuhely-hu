<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    /**
     * Follow a Crystal Master
     */
    public function follow(User $user): JsonResponse
    {
        try {
            /** @var User $currentUser */
            $currentUser = Auth::user();

            if ($currentUser->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot follow yourself',
                ], 400);
            }

            if ($currentUser->isFollowing($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already following this Crystal Master',
                ], 400);
            }

            $currentUser->follow($user);

            return response()->json([
                'success' => true,
                'message' => 'Successfully followed ' . $user->getDisplayName(),
                'follower_count' => $user->fresh()->follower_count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to follow: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unfollow a Crystal Master
     */
    public function unfollow(User $user): JsonResponse
    {
        try {
            /** @var User $currentUser */
            $currentUser = Auth::user();

            if (!$currentUser->isFollowing($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not following this Crystal Master',
                ], 400);
            }

            $currentUser->unfollow($user);

            return response()->json([
                'success' => true,
                'message' => 'Successfully unfollowed ' . $user->getDisplayName(),
                'follower_count' => $user->fresh()->follower_count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unfollow: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get follow status for a user
     */
    public function status(User $user): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        return response()->json([
            'is_following' => $currentUser->isFollowing($user),
            'follower_count' => $user->follower_count,
            'following_count' => $user->following_count,
        ]);
    }
}
