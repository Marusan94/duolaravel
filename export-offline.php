<?php
/**
 * export-offline.php — Genera el paquete de contenido offline para la app APK.
 *
 * Exporta el currículo completo de la base de datos (unidades → skills →
 * lecciones → ejercicios + opciones embebidas) a un único content.js lista
 * para embeber en la app Android (proyecto duoLaravel APK).
 *
 * Uso (desde la raíz del proyecto Laravel):
 *   php export-offline.php [ruta/destino/content.js]
 *
 * Por defecto escribe en ../duo-mobile/www/content.js (proyecto hermano).
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$outPath = $argv[1] ?? __DIR__ . '/../duo-mobile/www/content.js';

$units = DB::table('units')->orderBy('id')->get();
$skills = DB::table('skills')->orderBy('id')->get();
$lessons = DB::table('lessons')->orderBy('id')->get();
$exercises = DB::table('exercises')->orderBy('id')->get();
$options = DB::table('exercise_options')->orderBy('id')->get();

// Agrupar opciones por exercise_id y embeberlas en cada ejercicio.
$optionsByExercise = [];
foreach ($options as $opt) {
    $optionsByExercise[$opt->exercise_id][] = [
        'id' => (int) $opt->id,
        'text' => $opt->text,
        'is_correct' => (int) $opt->is_correct,
        'position' => (int) $opt->position,
    ];
}

$exercisesOut = [];
$totalOptions = 0;
foreach ($exercises as $ex) {
    $embedded = $optionsByExercise[$ex->id] ?? [];
    $exercisesOut[] = [
        'id' => (int) $ex->id,
        'lesson_id' => (int) $ex->lesson_id,
        'type' => $ex->type,
        'prompt' => $ex->prompt,
        'payload' => $ex->payload,
        'position' => (int) $ex->position,
        'created_at' => (string) $ex->created_at,
        'updated_at' => (string) $ex->updated_at,
        'options' => $embedded,
    ];
    $totalOptions += count($embedded);
}

$content = [
    'meta' => [
        'name' => 'duoLaravel offline',
        'generated_at' => now()->toIso8601String(),
        'units' => $units->count(),
        'lessons' => $lessons->count(),
        'exercises' => $exercises->count(),
        'options' => $options->count(),
    ],
    'units' => $units->map(fn ($u) => [
        'id' => (int) $u->id,
        'course_id' => (int) $u->course_id,
        'title' => $u->title,
        'description' => $u->description,
        'color' => $u->color,
        'position' => (int) $u->position,
        'created_at' => (string) $u->created_at,
        'updated_at' => (string) $u->updated_at,
        'guide' => $u->guide,
    ])->all(),
    'skills' => $skills->map(fn ($s) => [
        'id' => (int) $s->id,
        'unit_id' => (int) $s->unit_id,
        'title' => $s->title,
        'slug' => $s->slug,
        'icon' => $s->icon,
        'position' => (int) $s->position,
        'created_at' => (string) $s->created_at,
        'updated_at' => (string) $s->updated_at,
    ])->all(),
    'lessons' => $lessons->map(fn ($l) => [
        'id' => (int) $l->id,
        'skill_id' => (int) $l->skill_id,
        'title' => $l->title,
        'position' => (int) $l->position,
        'xp_reward' => (int) $l->xp_reward,
        'created_at' => (string) $l->created_at,
        'updated_at' => (string) $l->updated_at,
    ])->all(),
    'exercises' => $exercisesOut,
];

$json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($outPath, "const CONTENT = " . $json . ";");

echo "export-offline: {$content['meta']['units']} unidades, {$content['meta']['lessons']} lecciones, "
    . "{$content['meta']['exercises']} ejercicios, $totalOptions opciones embebidas\n";
echo "escrito en: $outPath (" . round(strlen($json) / 1024, 1) . " kB)\n";
