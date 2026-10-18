<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Mirrors Duolingo's `/api/1/courses/list` item shape (snake_case).
 */
class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'learning_language' => $this->toLanguage?->code,
            'from_language' => $this->fromLanguage?->code,
            'learning_language_id' => $this->to_language_id,
            'from_language_id' => $this->from_language_id,
            'learning_language_name' => $this->toLanguage?->name,
            'from_language_name' => $this->fromLanguage?->name,
            'num_learners' => (int) $this->userCourses()->count(),
            'phase' => 1,
            'progress' => 0,
        ];
    }
}
