<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Crystal Milestone Thresholds
    |--------------------------------------------------------------------------
    |
    | Define the milestone thresholds for content engagement metrics.
    | When content reaches these thresholds, crystal updates are triggered.
    |
    */

    'milestones' => [
        'views' => [10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000],
        'downloads' => [1, 3, 5, 10, 25, 50, 100, 250, 500, 1000],
        'rating_threshold' => 3,
    ],

];
