<?php

namespace App\Http\Controllers;

use App\Models\CrystalActivityQueue;
use App\Models\Expedition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpeditionController extends Controller
{
    /**
     * Display list of expeditions
     */
    public function index()
    {
        $activeExpeditions = Expedition::active()
            ->withCount('enrollments')
            ->orderBy('starts_at', 'desc')
            ->get();

        $upcomingExpeditions = Expedition::upcoming()
            ->withCount('enrollments')
            ->orderBy('starts_at', 'asc')
            ->get();

        $completedExpeditions = Expedition::completed()
            ->withCount('enrollments')
            ->orderBy('ends_at', 'desc')
            ->limit(6)
            ->get();

        return view('expeditions.index', compact(
            'activeExpeditions',
            'upcomingExpeditions',
            'completedExpeditions'
        ));
    }

    /**
     * Display expedition details
     */
    public function show(Expedition $expedition)
    {
        $expedition->load(['enrollments' => function ($query) {
            $query->whereNotNull('completed_at')->with('user');
        }]);

        $userEnrollment = null;
        if (Auth::check()) {
            $userEnrollment = $expedition->enrollments()
                ->where('user_id', Auth::id())
                ->with('qualifyingPosts.post')
                ->first();
        }

        $isEnrolled = $userEnrollment !== null;
        $canEnroll = Auth::check() && !$isEnrolled && $expedition->isEnrollable();

        $topCompleters = $expedition->enrollments()
            ->whereNotNull('completed_at')
            ->with('user')
            ->orderBy('completed_at', 'asc')
            ->limit(10)
            ->get();

        return view('expeditions.show', compact(
            'expedition',
            'userEnrollment',
            'isEnrolled',
            'canEnroll',
            'topCompleters'
        ));
    }

    /**
     * Enroll user in expedition
     */
    public function enroll(Request $request, Expedition $expedition)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to enroll in expeditions.');
        }

        try {
            $user = Auth::user();
            $enrollment = $user->enrollInExpedition($expedition);

            // Queue activity
            CrystalActivityQueue::create([
                'user_id' => $user->id,
                'activity_type' => CrystalActivityQueue::TYPE_EXPEDITION_ENROLLED,
                'metadata' => [
                    'expedition_id' => $expedition->id,
                    'expedition_title' => $expedition->title,
                ],
            ]);

            return redirect()
                ->route('expeditions.show', $expedition)
                ->with('success', "Successfully enrolled in {$expedition->title}!");
        } catch (\Exception $e) {
            return redirect()
                ->route('expeditions.show', $expedition)
                ->with('error', $e->getMessage());
        }
    }
}
