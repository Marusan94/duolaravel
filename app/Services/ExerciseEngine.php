<?php

namespace App\Services;

use App\Models\Exercise;

class ExerciseEngine
{
    /**
     * Validate a user's answer for an exercise.
     *
     * $answer may be: an option id (int), a free-text string,
     * or an array for ordered / matching exercises.
     */
    public function validate(Exercise $exercise, mixed $answer): bool
    {
        return match ($exercise->type) {
            'select_image', 'select_translation', 'listen_select' => $this->validateOption($exercise, $answer),
            'translate', 'type_translation', 'fill_blank' => $this->validateText($exercise, $answer),
            'order_words', 'order_tokens' => $this->validateOrder($exercise, $answer),
            'match_pairs' => $this->validateMatch($exercise, $answer),
            default => false,
        };
    }

    protected function validateOption(Exercise $exercise, mixed $answer): bool
    {
        $option = $exercise->options()->find((int) $answer);

        return $option !== null && $option->is_correct;
    }

    protected function validateText(Exercise $exercise, mixed $answer): bool
    {
        $normalized = $this->normalize((string) $answer);

        $accepted = $exercise->payload['answers']
            ?? $exercise->options()->where('is_correct', true)->pluck('text')->all();

        foreach ((array) $accepted as $candidate) {
            if ($this->normalize((string) $candidate) === $normalized) {
                return true;
            }
        }

        return false;
    }

    protected function validateOrder(Exercise $exercise, mixed $answer): bool
    {
        $correct = $exercise->payload['answer']
            ?? $exercise->options()->where('is_correct', true)->orderBy('position')->pluck('text')->all();

        $given = array_map(fn ($token) => $this->normalize((string) $token), (array) $answer);
        $expected = array_map(fn ($token) => $this->normalize((string) $token), (array) $correct);

        return $given === $expected;
    }

    protected function validateMatch(Exercise $exercise, mixed $answer): bool
    {
        $pairs = (array) ($exercise->payload['pairs'] ?? []);
        $given = (array) $answer;

        foreach ($given as $left => $right) {
            if (! array_key_exists($left, $pairs) || $this->normalize((string) $pairs[$left]) !== $this->normalize((string) $right)) {
                return false;
            }
        }

        return count($pairs) > 0 && count($given) === count($pairs);
    }

    protected function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'u', 'n'], $value);
        $value = preg_replace('/[^\p{L}\p{N}\s]/u', '', $value);

        return preg_replace('/\s+/', ' ', trim($value));
    }
}
