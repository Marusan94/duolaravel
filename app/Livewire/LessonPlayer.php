<?php

namespace App\Livewire;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Services\LessonRunner;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.focus')]
#[Title('Lección')]
class LessonPlayer extends Component
{
    public Lesson $lesson;

    public array $ids = [];

    public int $index = 0;

    public $selected = null;

    public string $text = '';

    public array $order = [];

    public array $matches = [];

    /** @var array<int, array<int, string>> opciones del lado derecho, estables por ejercicio (match_pairs) */
    public array $rightsMap = [];

    /** @var array<int, array<int, string>> banco de palabras, estable por ejercicio (order_*) */
    public array $tokensMap = [];

    public ?bool $correct = null;

    public bool $finished = false;

    public ?int $hearts = null;

    public int $score = 0;

    public int $total = 0;

    public function mount(Lesson $lesson): void
    {
        $this->lesson = $lesson;
        $this->ids = $lesson->exercises()->orderBy('position')->pluck('id')->all();
        $this->total = count($this->ids);
        $this->hearts = auth()->user()->heart?->current;

        $this->rightsMap = $lesson->exercises()
            ->where('type', 'match_pairs')
            ->get()
            ->mapWithKeys(function (Exercise $exercise) {
                $rights = array_values((array) ($exercise->payload['pairs'] ?? []));
                shuffle($rights);

                return [$exercise->id => $rights];
            })
            ->all();

        $this->tokensMap = $lesson->exercises()
            ->whereIn('type', ['order_words', 'order_tokens'])
            ->get()
            ->mapWithKeys(function (Exercise $exercise) {
                $tokens = array_values((array) ($exercise->payload['answer'] ?? []));
                shuffle($tokens);

                return [$exercise->id => $tokens];
            })
            ->all();

        if ($this->total === 0) {
            $this->finished = true;
        }
    }

    public function currentExercise(): ?Exercise
    {
        if (! isset($this->ids[$this->index])) {
            return null;
        }

        return Exercise::with('options')->find($this->ids[$this->index]);
    }

    public function addToken(string $token): void
    {
        if ($this->correct === null) {
            $this->order[] = $token;
        }
    }

    public function removeToken(int $i): void
    {
        if ($this->correct === null) {
            unset($this->order[$i]);
            $this->order = array_values($this->order);
        }
    }

    public function hasAnswer(): bool
    {
        $exercise = $this->currentExercise();

        if (! $exercise) {
            return false;
        }

        return match ($exercise->type) {
            'select_translation', 'select_image', 'listen_select' => $this->selected !== null,
            'order_words', 'order_tokens' => count($this->order) > 0,
            'match_pairs' => $this->allPairsMatched($exercise),
            default => trim($this->text) !== '',
        };
    }

    private function allPairsMatched(Exercise $exercise): bool
    {
        $pairs = (array) ($exercise->payload['pairs'] ?? []);
        $filled = array_filter($this->matches, fn ($value) => trim((string) $value) !== '');

        return count($pairs) > 0 && count($filled) === count($pairs);
    }

    public function submit(LessonRunner $runner): void
    {
        $exercise = $this->currentExercise();

        if (! $exercise || $this->correct !== null || ! $this->hasAnswer()) {
            return;
        }

        $answer = match ($exercise->type) {
            'select_translation', 'select_image', 'listen_select' => $this->selected,
            'order_words', 'order_tokens' => $this->order,
            'match_pairs' => $this->matches,
            default => $this->text,
        };

        $result = $runner->answer(auth()->user(), $exercise, $answer);
        $this->correct = $result['correct'];
        $this->hearts = $result['hearts'];

        $this->dispatch('duo-sound', name: $this->correct ? 'correct' : 'wrong');

        if ($this->correct) {
            $this->score++;
        }
    }

    public function next(LessonRunner $runner): void
    {
        if ($this->index < $this->total - 1) {
            $this->index++;
            $this->reset(['selected', 'text', 'order', 'matches', 'correct']);

            return;
        }

        $score = $this->total ? (int) round($this->score / $this->total * 100) : 100;
        $runner->complete(auth()->user(), $this->lesson, $score);

        $this->dispatch('duo-sound', name: 'complete');

        session()->flash('status', '¡Lección completada! Ganaste '.$this->lesson->xp_reward.' XP');

        $this->redirect(route('learn'), navigate: true);
    }

    public function render()
    {
        return view('livewire.lesson-player', [
            'exercise' => $this->currentExercise(),
        ]);
    }
}
