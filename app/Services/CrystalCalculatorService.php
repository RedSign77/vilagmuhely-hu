<?php

namespace App\Services;

use App\Models\ContentRating;
use App\Models\User;
use App\Models\UserCrystalMetric;
use Webtechsolutions\ContentEngine\Models\Content;

class CrystalCalculatorService
{
    /**
     * Calculate diversity index using Shannon entropy
     * Measures variety across content types
     */
    public function calculateDiversityIndex(User $user): float
    {
        $contents = $user->contents()->published()->get();

        if ($contents->isEmpty()) {
            return 0;
        }

        $typeCounts = $contents->groupBy('type')->map->count();
        $total = $contents->count();

        $entropy = 0;
        foreach ($typeCounts as $count) {
            $proportion = $count / $total;
            $entropy -= $proportion * log($proportion, 2);
        }

        // Normalize to 0-1 range (max entropy for 5 types is log2(5) ≈ 2.32)
        $maxEntropy = log(5, 2);
        return min(1, $entropy / $maxEntropy);
    }

    /**
     * Calculate interaction score
     * Based on views, downloads, and helpful ratings received
     */
    public function calculateInteractionScore(User $user): float
    {
        $contents = $user->contents()->published()->get();

        $totalViews = $contents->sum('views_count');
        $totalDownloads = $contents->sum('downloads_count');

        // Get helpful ratings count across all user's content
        $helpfulRatings = ContentRating::whereIn('content_id', $contents->pluck('id'))
            ->where('is_helpful', true)
            ->count();

        // Weighted formula
        $score = ($totalViews * 0.3) + ($totalDownloads * 0.5) + ($helpfulRatings * 1.0);

        return round($score, 2);
    }

    /**
     * Calculate engagement score
     * Based on ratings given to others and participation consistency
     */
    public function calculateEngagementScore(User $user): float
    {
        // Ratings given by this user
        $ratingsGiven = ContentRating::where('user_id', $user->id)->count();

        // Participation days (days with any activity)
        $firstContent = $user->contents()->oldest()->first();
        $participationDays = 1;

        if ($firstContent) {
            $participationDays = max(1, $firstContent->created_at->diffInDays(now()));
        }

        // Weighted formula
        $score = ($ratingsGiven * 0.4) + ($participationDays * 0.6);

        return round($score, 2);
    }

    /**
     * Calculate facet count (crystal complexity)
     * More content + higher diversity = more facets
     */
    public function calculateFacetCount(int $contentCount, float $diversityIndex): int
    {
        // Base facets (minimum polyhedron)
        $baseFacets = 4;

        // Content-based facets (1 facet per 2 content items)
        $contentFacets = floor($contentCount / 2);

        // Diversity bonus (up to 20 extra facets for max diversity)
        $diversityFacets = floor($diversityIndex * 20);

        $totalFacets = $baseFacets + $contentFacets + $diversityFacets;

        // Cap at 50 facets for performance
        return min(50, max(4, $totalFacets));
    }

    /**
     * Calculate glow intensity
     * Higher interaction = brighter glow
     */
    public function calculateGlowIntensity(float $interactionScore): float
    {
        // Logarithmic scaling to prevent extreme values
        // Score of 100 = 0.5 intensity, 1000 = 0.75, 10000 = 1.0
        if ($interactionScore <= 0) {
            return 0;
        }

        $intensity = log10($interactionScore + 1) / 4;

        return min(1.0, max(0, round($intensity, 2)));
    }

    /**
     * Calculate purity level (transparency)
     * Higher engagement = clearer crystal
     */
    public function calculatePurityLevel(float $engagementScore): float
    {
        // Similar logarithmic scaling
        if ($engagementScore <= 0) {
            return 0.3; // Minimum purity
        }

        $purity = 0.3 + (log10($engagementScore + 1) / 5);

        return min(1.0, max(0.3, round($purity, 2)));
    }

    /**
     * Calculate dominant colors based on content categories
     */
    public function calculateDominantColors(User $user): array
    {
        $contents = $user->contents()->published()->with('category')->get();

        if ($contents->isEmpty()) {
            return ['#94a3b8']; // Default gray
        }

        // Get top 3 categories by content count
        $categoryColors = $contents
            ->filter(fn($c) => $c->category)
            ->groupBy('category_id')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'color' => $group->first()->category->color ?? '#94a3b8',
                ];
            })
            ->sortByDesc('count')
            ->take(3)
            ->pluck('color')
            ->values()
            ->toArray();

        return !empty($categoryColors) ? $categoryColors : ['#94a3b8'];
    }

    /**
     * Generate 3D crystal geometry
     * Creates vertices, faces, normals, and colors for Three.js
     */
    public function generateCrystalGeometry(UserCrystalMetric $metric): array
    {
        $facetCount = $metric->facet_count;
        $colors = $metric->dominant_colors ?? ['#94a3b8'];

        // Generate Fibonacci sphere vertices
        $vertices = $this->generateFibonacciSphere($facetCount);

        // Generate faces using convex hull approximation
        $faces = $this->generateConvexHullFaces($vertices);

        // Calculate normals
        $normals = $this->calculateNormals($vertices, $faces);

        // Assign colors to vertices
        $vertexColors = $this->assignColorsToVertices($vertices, $colors);

        return [
            'vertices' => $vertices,
            'faces' => $faces,
            'normals' => $normals,
            'colors' => $vertexColors,
        ];
    }

    /**
     * Generate Fibonacci sphere points
     */
    protected function generateFibonacciSphere(int $pointCount): array
    {
        $points = [];
        $phi = (1 + sqrt(5)) / 2; // Golden ratio

        for ($i = 0; $i < $pointCount; $i++) {
            $y = 1 - (2 * $i / ($pointCount - 1));
            $radius = sqrt(1 - $y * $y);
            $theta = 2 * pi() * $i / $phi;

            $x = cos($theta) * $radius;
            $z = sin($theta) * $radius;

            $points[] = [
                round($x, 4),
                round($y, 4),
                round($z, 4),
            ];
        }

        return $points;
    }

    /**
     * Generate faces using simplified convex hull
     */
    protected function generateConvexHullFaces(array $vertices): array
    {
        $faces = [];
        $vertexCount = count($vertices);

        // Simple triangulation - connect each point to its neighbors
        for ($i = 0; $i < $vertexCount; $i++) {
            $next = ($i + 1) % $vertexCount;
            $nextnext = ($i + 2) % $vertexCount;

            $faces[] = [$i, $next, $nextnext];
        }

        // Add center connections for more complex geometry
        $centerIndex = 0; // Use first vertex as center reference
        for ($i = 1; $i < min($vertexCount, 20); $i += 2) {
            $next = ($i + 1) % $vertexCount;
            $faces[] = [$centerIndex, $i, $next];
        }

        return $faces;
    }

    /**
     * Calculate face normals
     */
    protected function calculateNormals(array $vertices, array $faces): array
    {
        $normals = [];

        foreach ($faces as $face) {
            $v1 = $vertices[$face[0]];
            $v2 = $vertices[$face[1]];
            $v3 = $vertices[$face[2]];

            // Calculate normal vector using cross product
            $edge1 = [
                $v2[0] - $v1[0],
                $v2[1] - $v1[1],
                $v2[2] - $v1[2],
            ];

            $edge2 = [
                $v3[0] - $v1[0],
                $v3[1] - $v1[1],
                $v3[2] - $v1[2],
            ];

            $normal = [
                $edge1[1] * $edge2[2] - $edge1[2] * $edge2[1],
                $edge1[2] * $edge2[0] - $edge1[0] * $edge2[2],
                $edge1[0] * $edge2[1] - $edge1[1] * $edge2[0],
            ];

            // Normalize
            $length = sqrt($normal[0] ** 2 + $normal[1] ** 2 + $normal[2] ** 2);
            if ($length > 0) {
                $normal = [
                    round($normal[0] / $length, 4),
                    round($normal[1] / $length, 4),
                    round($normal[2] / $length, 4),
                ];
            }

            $normals[] = $normal;
        }

        return $normals;
    }

    /**
     * Assign colors to vertices based on dominant colors
     */
    protected function assignColorsToVertices(array $vertices, array $hexColors): array
    {
        $rgbColors = array_map(fn($hex) => $this->hexToRgb($hex), $hexColors);
        $vertexColors = [];
        $colorCount = count($rgbColors);

        foreach ($vertices as $index => $vertex) {
            // Cycle through colors
            $color = $rgbColors[$index % $colorCount];
            $vertexColors[] = $color;
        }

        return $vertexColors;
    }

    /**
     * Convert hex color to RGB array (0-1 range for Three.js)
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        return [
            round($r, 3),
            round($g, 3),
            round($b, 3),
        ];
    }

    /**
     * Full recalculation of all metrics for a user
     */
    public function recalculateMetrics(User $user): UserCrystalMetric
    {
        $metric = UserCrystalMetric::firstOrCreate(['user_id' => $user->id]);

        // Calculate base metrics
        $contentCount = $user->contents()->published()->count();
        $diversityIndex = $this->calculateDiversityIndex($user);
        $interactionScore = $this->calculateInteractionScore($user);
        $engagementScore = $this->calculateEngagementScore($user);

        // Calculate crystal dimensions
        $facetCount = $this->calculateFacetCount($contentCount, $diversityIndex);
        $glowIntensity = $this->calculateGlowIntensity($interactionScore);
        $purityLevel = $this->calculatePurityLevel($engagementScore);
        $dominantColors = $this->calculateDominantColors($user);

        // Update metric values
        $metric->total_content_count = $contentCount;
        $metric->diversity_index = $diversityIndex;
        $metric->interaction_score = $interactionScore;
        $metric->engagement_score = $engagementScore;
        $metric->facet_count = $facetCount;
        $metric->glow_intensity = $glowIntensity;
        $metric->purity_level = $purityLevel;
        $metric->dominant_colors = $dominantColors;

        // Generate 3D geometry
        $metric->cached_geometry = $this->generateCrystalGeometry($metric);
        $metric->last_calculated_at = now();

        $metric->save();

        return $metric;
    }
}
