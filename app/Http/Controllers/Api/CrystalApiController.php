<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentRating;
use App\Models\User;
use App\Models\UserCrystalMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webtechsolutions\ContentEngine\Events\ContentRatedEvent;
use Webtechsolutions\ContentEngine\Models\Content;

class CrystalApiController extends Controller
{
    /**
     * Get crystal data for a specific user
     */
    public function show(int $userId): JsonResponse
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $metric = UserCrystalMetric::where('user_id', $userId)->first();

        if (!$metric) {
            return response()->json([
                'success' => false,
                'message' => 'Crystal metrics not yet calculated for this user',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ],
                ...$metric->getCrystalData(),
            ],
        ]);
    }

    /**
     * Get crystal gallery (top users)
     */
    public function gallery(Request $request): JsonResponse
    {
        $sortBy = $request->query('sort', 'interaction'); // interaction, diversity, engagement
        $limit = min($request->query('limit', 20), 50);

        $query = UserCrystalMetric::with('user');

        switch ($sortBy) {
            case 'diversity':
                $query->topDiversity($limit);
                break;
            case 'engagement':
                $query->orderByDesc('engagement_score')->limit($limit);
                break;
            case 'interaction':
            default:
                $query->topInteraction($limit);
                break;
        }

        $metrics = $query->get();

        $crystals = $metrics->map(function ($metric) {
            return [
                'user' => [
                    'id' => $metric->user->id,
                    'name' => $metric->user->name,
                    'avatar' => $metric->user->avatar,
                ],
                ...$metric->getCrystalData(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $crystals,
            'sort_by' => $sortBy,
        ]);
    }

    /**
     * Get leaderboard
     */
    public function leaderboard(): JsonResponse
    {
        $topInteraction = UserCrystalMetric::with('user')
            ->topInteraction(10)
            ->get()
            ->map(fn($m) => [
                'user' => ['id' => $m->user->id, 'name' => $m->user->name],
                'score' => $m->interaction_score,
            ]);

        $topDiversity = UserCrystalMetric::with('user')
            ->topDiversity(10)
            ->get()
            ->map(fn($m) => [
                'user' => ['id' => $m->user->id, 'name' => $m->user->name],
                'score' => $m->diversity_index,
            ]);

        $topEngagement = UserCrystalMetric::with('user')
            ->orderByDesc('engagement_score')
            ->limit(10)
            ->get()
            ->map(fn($m) => [
                'user' => ['id' => $m->user->id, 'name' => $m->user->name],
                'score' => $m->engagement_score,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'interaction' => $topInteraction,
                'diversity' => $topDiversity,
                'engagement' => $topEngagement,
            ],
        ]);
    }

    /**
     * Rate content
     */
    public function rateContent(Request $request, int $contentId): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'critique_text' => 'nullable|string|max:1000',
        ]);

        $content = Content::find($contentId);

        if (!$content) {
            return response()->json([
                'success' => false,
                'message' => 'Content not found',
            ], 404);
        }

        // Prevent self-rating
        if ($content->creator_id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot rate your own content',
            ], 403);
        }

        // Check if already rated
        $existingRating = ContentRating::where('content_id', $contentId)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this content',
            ], 409);
        }

        // Create rating
        $rating = ContentRating::create([
            'content_id' => $contentId,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'critique_text' => $request->critique_text,
        ]);

        // Fire event
        event(new ContentRatedEvent($content, $rating));

        return response()->json([
            'success' => true,
            'message' => 'Content rated successfully',
            'data' => [
                'rating_id' => $rating->id,
                'rating' => $rating->rating,
                'created_at' => $rating->created_at,
            ],
        ]);
    }

    /**
     * Mark rating as helpful (by content creator)
     */
    public function markRatingHelpful(int $contentId, int $ratingId): JsonResponse
    {
        $content = Content::find($contentId);
        $rating = ContentRating::find($ratingId);

        if (!$content || !$rating) {
            return response()->json([
                'success' => false,
                'message' => 'Content or rating not found',
            ], 404);
        }

        // Only content creator can mark ratings as helpful
        if ($content->creator_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the content creator can mark ratings as helpful',
            ], 403);
        }

        $rating->markAsHelpful();

        return response()->json([
            'success' => true,
            'message' => 'Rating marked as helpful',
        ]);
    }
}
