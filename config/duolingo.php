<?php

return [
    'speech_host' => env('DUOLINGO_SPEECH_HOST', 'speech.duolingo.com'),
    'age_restriction_limit' => 13,
    'default_country' => 'CO',
    'daily_goal' => 20,
    'client_experiments' => [],
    'feature_flags' => [
        'web_hide_alphabet_gates' => false,
        'feed_microservice_web_client' => true,
        'leaderboard_reactions_rollout' => true,
        'job_board' => true,
        'web_use_onboarding_backend' => true,
    ],
];
