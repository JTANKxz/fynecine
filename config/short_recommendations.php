<?php

return [
    // Mantém os ajustes de ranking centralizados — sem pesos espalhados em controllers.
    'candidate_limit' => env('SHORTS_CANDIDATE_LIMIT', 250),
    'feed_limit' => env('SHORTS_FEED_LIMIT', 12),
    'max_same_category_in_row' => env('SHORTS_MAX_SAME_CATEGORY', 2),
    'max_same_related_in_row' => env('SHORTS_MAX_SAME_RELATED', 1),
    'recent_delivery_hours' => env('SHORTS_RECENT_DELIVERY_HOURS', 24),
    'return_to_session_minutes' => env('SHORTS_RETURN_TO_SESSION_MINUTES', 15),
    'mix' => [
        'quality' => 0.60,
        'trending' => 0.25,
        'explore' => 0.15,
    ],
    'weights' => [
        'user_category' => 8.0,
        'user_hashtag' => 5.0,
        'user_related' => 13.0,
        'global_views' => 2.0,
        'global_completion' => 18.0,
        'global_likes' => 7.0,
        'recent_performance' => 5.0,
        'newness' => 5.0,
        'exploration_jitter' => 7.0,
        'recent_delivery_penalty' => 45.0,
        'recent_view_penalty' => 32.0,
        'skip_penalty' => 28.0,
        'hide_penalty' => 100.0,
    ],
    'signals' => [
        'like' => 5.0,
        'completion' => 3.0,
        'replay' => 4.0,
        'view' => 0.5,
        'progress' => 0.75,
        'skip' => -3.0,
        'hide' => -8.0,
        'report' => -10.0,
    ],
];
