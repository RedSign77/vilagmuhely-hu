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
            ->filter(fn ($c) => $c->category)
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

        return ! empty($categoryColors) ? $categoryColors : ['#94a3b8'];
    }

    /**
     * Generate 3D crystal geometry
     * Creates vertices, faces, normals, and colors for Three.js
     */
    public function generateCrystalGeometry(UserCrystalMetric $metric): array
    {
        $facetCount = $metric->facet_count;
        $colors = $metric->dominant_colors ?? ['#94a3b8'];

        // Generate base crystal shape based on facet count
        [$vertices, $faces] = $this->generateCrystalBase($facetCount);

        // Apply distortions based on user metrics
        $vertices = $this->applyCrystalDistortions($vertices, $metric);

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
     * Generate base crystal geometry
     * Uses platonic/archimedean solids as foundation
     */
    protected function generateCrystalBase(int $facetCount): array
    {
        // Select base shape based on facet count
        if ($facetCount <= 8) {
            return $this->generateOctahedron();
        } elseif ($facetCount <= 12) {
            return $this->generateDodecahedron();
        } elseif ($facetCount <= 20) {
            return $this->generateIcosahedron();
        } else {
            // For higher facet counts, use crystal cluster
            $crystalCount = min(12, floor($facetCount / 8));

            return $this->generateCrystalCluster($crystalCount);
        }
    }

    /**
     * Generate octahedron (8 faces, simple crystal)
     */
    protected function generateOctahedron(): array
    {
        $vertices = [
            [1, 0, 0],
            [-1, 0, 0],
            [0, 1, 0],
            [0, -1, 0],
            [0, 0, 1],
            [0, 0, -1],
        ];

        $faces = [
            [0, 2, 4], [0, 4, 3], [0, 3, 5], [0, 5, 2],
            [1, 4, 2], [1, 3, 4], [1, 5, 3], [1, 2, 5],
        ];

        return [$vertices, $faces];
    }

    /**
     * Generate dodecahedron (12 pentagonal faces)
     */
    protected function generateDodecahedron(): array
    {
        $phi = (1 + sqrt(5)) / 2; // Golden ratio
        $invPhi = 1 / $phi;

        $vertices = [
            [1, 1, 1], [1, 1, -1], [1, -1, 1], [1, -1, -1],
            [-1, 1, 1], [-1, 1, -1], [-1, -1, 1], [-1, -1, -1],
            [0, $invPhi, $phi], [0, $invPhi, -$phi], [0, -$invPhi, $phi], [0, -$invPhi, -$phi],
            [$invPhi, $phi, 0], [$invPhi, -$phi, 0], [-$invPhi, $phi, 0], [-$invPhi, -$phi, 0],
            [$phi, 0, $invPhi], [$phi, 0, -$invPhi], [-$phi, 0, $invPhi], [-$phi, 0, -$invPhi],
        ];

        // Normalize vertices to unit sphere
        $vertices = array_map(function ($v) {
            $len = sqrt($v[0] ** 2 + $v[1] ** 2 + $v[2] ** 2);

            return [$v[0] / $len, $v[1] / $len, $v[2] / $len];
        }, $vertices);

        $faces = [
            [0, 8, 10, 2, 16], [0, 16, 17, 1, 12], [1, 17, 3, 11, 9], [1, 9, 5, 14, 12],
            [2, 10, 6, 15, 13], [2, 13, 3, 17, 16], [3, 13, 15, 7, 11], [4, 14, 5, 19, 18],
            [4, 18, 6, 10, 8], [4, 8, 0, 12, 14], [5, 9, 11, 7, 19], [6, 18, 19, 7, 15],
        ];

        // Triangulate pentagons for Three.js
        $triangulatedFaces = [];
        foreach ($faces as $face) {
            $center = $face[0];
            for ($i = 1; $i < count($face) - 1; $i++) {
                $triangulatedFaces[] = [$center, $face[$i], $face[$i + 1]];
            }
        }

        return [$vertices, $triangulatedFaces];
    }

    /**
     * Generate icosahedron (20 faces)
     */
    protected function generateIcosahedron(): array
    {
        $phi = (1 + sqrt(5)) / 2; // Golden ratio

        $vertices = [
            [-1, $phi, 0], [1, $phi, 0], [-1, -$phi, 0], [1, -$phi, 0],
            [0, -1, $phi], [0, 1, $phi], [0, -1, -$phi], [0, 1, -$phi],
            [$phi, 0, -1], [$phi, 0, 1], [-$phi, 0, -1], [-$phi, 0, 1],
        ];

        // Normalize vertices
        $vertices = array_map(function ($v) {
            $len = sqrt($v[0] ** 2 + $v[1] ** 2 + $v[2] ** 2);

            return [
                round($v[0] / $len, 4),
                round($v[1] / $len, 4),
                round($v[2] / $len, 4),
            ];
        }, $vertices);

        $faces = [
            [0, 11, 5], [0, 5, 1], [0, 1, 7], [0, 7, 10], [0, 10, 11],
            [1, 5, 9], [5, 11, 4], [11, 10, 2], [10, 7, 6], [7, 1, 8],
            [3, 9, 4], [3, 4, 2], [3, 2, 6], [3, 6, 8], [3, 8, 9],
            [4, 9, 5], [2, 4, 11], [6, 2, 10], [8, 6, 7], [9, 8, 1],
        ];

        return [$vertices, $faces];
    }

    /**
     * Generate crystal cluster (multiple hexagonal prisms)
     */
    protected function generateCrystalCluster(int $crystalCount): array
    {
        $vertices = [];
        $faces = [];
        $vertexOffset = 0;

        // Generate central crystal (tallest)
        [$centralVerts, $centralFaces] = $this->generateHexagonalCrystal(1.2, 0, 0, 0, 0);
        $vertices = array_merge($vertices, $centralVerts);
        $faces = array_merge($faces, $centralFaces);
        $vertexOffset += count($centralVerts);

        // Generate surrounding crystals in a ring
        $ringCrystals = $crystalCount - 1;
        for ($i = 0; $i < $ringCrystals; $i++) {
            $angle = (2 * pi() * $i) / $ringCrystals;
            $radius = 0.6;

            // Position on circle
            $x = cos($angle) * $radius;
            $z = sin($angle) * $radius;

            // Vary height (0.7 to 1.0)
            $height = 0.7 + (($i % 3) * 0.15);

            // Slight tilt outward
            $tiltAngle = 0.2;

            [$crystalVerts, $crystalFaces] = $this->generateHexagonalCrystal(
                $height, $x, 0, $z, $angle + $tiltAngle
            );

            // Offset face indices
            $offsetFaces = array_map(function ($face) use ($vertexOffset) {
                return [$face[0] + $vertexOffset, $face[1] + $vertexOffset, $face[2] + $vertexOffset];
            }, $crystalFaces);

            $vertices = array_merge($vertices, $crystalVerts);
            $faces = array_merge($faces, $offsetFaces);
            $vertexOffset += count($crystalVerts);
        }

        return [$vertices, $faces];
    }

    /**
     * Generate single hexagonal crystal prism with pointed top
     */
    protected function generateHexagonalCrystal(float $height, float $x, float $y, float $z, float $tilt): array
    {
        $vertices = [];
        $faces = [];
        $radius = 0.2;
        $pointHeight = $height + 0.3; // Pointed termination

        // Generate hexagonal base vertices (bottom)
        for ($i = 0; $i < 6; $i++) {
            $angle = (pi() / 3) * $i;
            $vx = cos($angle) * $radius;
            $vz = sin($angle) * $radius;

            // Apply tilt rotation
            $rotX = $vx * cos($tilt) - $vz * sin($tilt);
            $rotZ = $vx * sin($tilt) + $vz * cos($tilt);

            $vertices[] = [
                round($x + $rotX, 4),
                round($y, 4),
                round($z + $rotZ, 4),
            ];
        }

        // Generate hexagonal top vertices (before point)
        for ($i = 0; $i < 6; $i++) {
            $angle = (pi() / 3) * $i;
            $vx = cos($angle) * $radius * 0.9; // Slightly smaller
            $vz = sin($angle) * $radius * 0.9;

            // Apply tilt rotation
            $rotX = $vx * cos($tilt) - $vz * sin($tilt);
            $rotZ = $vx * sin($tilt) + $vz * cos($tilt);

            $vertices[] = [
                round($x + $rotX, 4),
                round($y + $height, 4),
                round($z + $rotZ, 4),
            ];
        }

        // Top point vertex
        $vertices[] = [
            round($x, 4),
            round($y + $pointHeight, 4),
            round($z, 4),
        ];

        // Generate faces for hexagonal sides
        for ($i = 0; $i < 6; $i++) {
            $next = ($i + 1) % 6;

            // Two triangles per rectangular face
            $faces[] = [$i, $next, $i + 6];
            $faces[] = [$next, $next + 6, $i + 6];
        }

        // Generate faces for pointed top
        for ($i = 0; $i < 6; $i++) {
            $next = ($i + 1) % 6;
            $faces[] = [$i + 6, $next + 6, 12]; // Point is vertex 12
        }

        // Bottom face (hexagon triangulated from center)
        for ($i = 0; $i < 6; $i++) {
            $next = ($i + 1) % 6;
            $faces[] = [0, $next, $i]; // Use vertex 0 as center
        }

        return [$vertices, $faces];
    }

    /**
     * Apply distortions to crystal vertices based on user metrics
     */
    protected function applyCrystalDistortions(array $vertices, UserCrystalMetric $metric): array
    {
        $diversity = $metric->diversity_index;
        $engagement = $metric->engagement_score;

        $distorted = [];
        foreach ($vertices as $index => $vertex) {
            // Calculate spherical coordinates
            $r = sqrt($vertex[0] ** 2 + $vertex[1] ** 2 + $vertex[2] ** 2);
            $theta = atan2($vertex[1], $vertex[0]);
            $phi = acos($vertex[2] / $r);

            // Apply subtle radial distortion based on diversity
            // Less diversity = more elongated, more diversity = more spherical
            $radialFactor = 1.0 + (sin($theta * 3 + $phi * 2) * 0.15 * (1 - $diversity));

            // Apply engagement-based scaling
            $scale = 1.0 + ($engagement / 100 * 0.2);

            $newR = $r * $radialFactor * $scale;

            // Convert back to Cartesian
            $distorted[] = [
                round($newR * sin($phi) * cos($theta), 4),
                round($newR * sin($phi) * sin($theta), 4),
                round($newR * cos($phi), 4),
            ];
        }

        return $distorted;
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
        $rgbColors = array_map(fn ($hex) => $this->hexToRgb($hex), $hexColors);
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
     * Calculate profile completeness (0-1)
     * Based on user profile fields being filled
     */
    public function calculateProfileCompleteness(User $user): float
    {
        $fields = [
            'avatar' => ! empty($user->avatar) ? 1 : 0,
            'about' => ! empty($user->about) ? 1 : 0,
            'mobile' => ! empty($user->mobile) ? 1 : 0,
            'city' => ! empty($user->city) ? 1 : 0,
            'address' => ! empty($user->address) ? 1 : 0,
            'social_media_links' => (! empty($user->social_media_links) && count($user->social_media_links) > 0) ? 1 : 0,
        ];

        $totalFields = count($fields);
        $filledFields = array_sum($fields);

        return round($filledFields / $totalFields, 2);
    }

    /**
     * Calculate the initial modifier multiplier (1.0 - 1.5x)
     * Based on profile completeness
     */
    public function calculateInitialModifier(float $completeness): float
    {
        // 0% complete = 1.0x (no bonus)
        // 100% complete = 1.5x (50% bonus)
        return round(1.0 + ($completeness * 0.5), 2);
    }

    /**
     * Full recalculation of all metrics for a user
     */
    public function recalculateMetrics(User $user): UserCrystalMetric
    {
        $metric = UserCrystalMetric::firstOrCreate(['user_id' => $user->id]);

        // Detect if this is first-time creation
        $isFirstCreation = ! $metric->initial_modifier_applied;

        // Calculate base metrics
        $contentCount = $user->contents()->published()->count();
        $diversityIndex = $this->calculateDiversityIndex($user);
        $interactionScore = $this->calculateInteractionScore($user);
        $engagementScore = $this->calculateEngagementScore($user);

        // Apply initial modifier on first creation only
        $modifier = 1.0;
        if ($isFirstCreation) {
            $completeness = $this->calculateProfileCompleteness($user);
            $modifier = $this->calculateInitialModifier($completeness);
            $metric->profile_completeness_modifier = $modifier;
            $metric->initial_modifier_applied = true;
        } else {
            // Use stored modifier for subsequent calculations
            $modifier = $metric->profile_completeness_modifier ?? 1.0;
        }

        // Apply modifier to interaction and engagement scores
        $interactionScore = $interactionScore * $modifier;
        $engagementScore = $engagementScore * $modifier;

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
