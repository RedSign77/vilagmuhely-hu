<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCrystalMetric;
use Illuminate\Http\Request;

class CrystalGalleryController extends Controller
{
    /**
     * Display the crystal gallery
     */
    public function index(Request $request)
    {
        $sortBy = $request->query('sort', 'interaction');
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

        return view('crystals.gallery', [
            'metrics' => $metrics,
            'sortBy' => $sortBy,
        ]);
    }

    /**
     * Show individual user's crystal
     */
    public function show(User $user)
    {
        $metric = UserCrystalMetric::where('user_id', $user->id)->first();

        if (! $metric) {
            return redirect()->route('crystals.gallery')
                ->with('error', 'Crystal metrics not yet calculated for this user.');
        }

        return view('crystals.show', [
            'user' => $user,
            'metric' => $metric,
        ]);
    }
}
