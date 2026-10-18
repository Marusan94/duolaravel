<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Mirrors Duolingo's `/api/1/courses/list` (flat array, snake_case items).
 */
class CourseController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $courses = Course::with(['fromLanguage', 'toLanguage'])
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        return CourseResource::collection($courses);
    }
}
