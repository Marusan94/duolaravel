<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mirrors Duolingo's `/2023-05-23/config` response shape.
 */
class ConfigController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'ageRestrictionLimit' => config('duolingo.age_restriction_limit'),
            'ipCountry' => $request->header('CF-IPCountry', config('duolingo.default_country')),
            'clientExperiments' => config('duolingo.client_experiments'),
            'speechHost' => config('duolingo.speech_host'),
            'featureFlags' => config('duolingo.feature_flags'),
        ]);
    }
}
