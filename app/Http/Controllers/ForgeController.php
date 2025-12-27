<?php

namespace App\Http\Controllers;

use App\Models\CrystalActivityQueue;
use App\Models\User;
use App\Models\UserCrystalMetric;
use Illuminate\Http\Request;

class ForgeController extends Controller
{
    /**
     * Display user's Forge profile.
     */
    public function show(Request $request)
    {
        // Get user from middleware
        $user = $request->input('user');

        // Check if user has crystal metrics
        $metric = UserCrystalMetric::where('user_id', $user->id)->first();

        if (! $metric) {
            return redirect()->route('crystals.gallery')
                ->with('info', "This creator's forge is still being prepared.");
        }

        // Load profile data
        $user->load([
            'contents' => fn ($q) => $q->published()->latest('published_at')->limit(6),
            'reviews' => fn ($q) => $q->where('status', 'approved')->with('content')->latest()->limit(5),
        ]);

        // Manually load downloads to avoid pivot model issues
        $downloads = \Webtechsolutions\ContentEngine\Models\Content::whereHas('downloads', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['category'])
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        // Get RPG stats
        $rpgStats = $user->rpg_stats;

        // Get recent activities
        $recentActivities = $user->getRecentActivities(20);

        // Format activities for display
        $activities = $this->formatActivities($recentActivities);

        // Count totals
        $counts = [
            'authored' => $user->contents()->published()->count(),
            'purchased' => \App\Models\ContentDownload::where('user_id', $user->id)->count(),
            'reviews' => $user->reviews()->where('status', 'approved')->count(),
        ];

        // SEO meta data
        $colorName = $user->crystal_color_name;
        $displayName = $user->getDisplayName();
        $pageTitle = $user->getMetaTitle();
        $pageDescription = $user->getMetaDescription();

        return view('forge.profile', [
            'user' => $user,
            'metric' => $metric,
            'rpgStats' => $rpgStats,
            'activities' => $activities,
            'counts' => $counts,
            'downloads' => $downloads,
            'displayName' => $displayName,
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'colorName' => $colorName,
        ]);
    }

    /**
     * Format activities for human-readable display.
     */
    private function formatActivities($activities)
    {
        return $activities->map(function ($activity) {
            $metadata = $activity->metadata ?? [];

            $formatted = [
                'type' => $activity->activity_type,
                'timestamp' => $activity->created_at,
                'icon' => $this->getActivityIcon($activity->activity_type),
                'message' => $this->getActivityMessage($activity->activity_type, $metadata),
            ];

            return $formatted;
        });
    }

    /**
     * Get icon for activity type.
     */
    private function getActivityIcon(string $type): string
    {
        return match ($type) {
            'content_published' => '📝',
            'content_downloaded' => '⬇️',
            'content_rated' => '⭐',
            'content_reviewed' => '💬',
            'achievement_unlocked' => '🏆',
            'invitation_sent' => '✉️',
            'invitation_accepted' => '🤝',
            'content_milestone_views' => '👁️',
            'content_milestone_downloads' => '🎯',
            default => '✨',
        };
    }

    /**
     * Get human-readable message for activity.
     */
    private function getActivityMessage(string $type, array $metadata): string
    {
        return match ($type) {
            'content_published' => 'Published new content',
            'content_downloaded' => 'Downloaded content from the library',
            'content_rated' => 'Rated a piece of content',
            'content_reviewed' => 'Wrote a review',
            'achievement_unlocked' => 'Unlocked achievement: '.($metadata['achievement_name'] ?? 'Unknown'),
            'invitation_sent' => 'Invited a new creator',
            'invitation_accepted' => 'Joined the workshop',
            'content_milestone_views' => 'Content reached '.($metadata['views'] ?? 'many').' views',
            'content_milestone_downloads' => 'Content reached '.($metadata['downloads'] ?? 'many').' downloads',
            default => 'Performed an action',
        };
    }
}
