<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\ShopItem;
use App\Models\Skill;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class DuolingoSeeder extends Seeder
{
    public function run(): void
    {
        $es = Language::firstOrCreate(['code' => 'es'], ['name' => 'Español', 'flag' => '🇪🇸']);
        $laravel = Language::firstOrCreate(['code' => 'lv'], ['name' => 'Laravel', 'flag' => '💻']);

        $course = Course::updateOrCreate(
            ['from_language_id' => $es->id, 'to_language_id' => $laravel->id],
            [
                'title' => 'Laravel',
                'description' => 'Aprende Laravel con ejercicios cortos, como un idioma.',
                'position' => 1,
                'is_active' => true,
            ]
        );

        $this->seedCurriculum($course);
        $this->seedShop();
        $this->seedAchievements();
    }

    private function seedCurriculum(Course $course): void
    {
        foreach ($this->curriculum() as $unitPosition => $unitData) {
            $unit = Unit::updateOrCreate(
                ['course_id' => $course->id, 'title' => $unitData['title']],
                [
                    'description' => $unitData['description'],
                    'color' => $unitData['color'],
                    'position' => $unitPosition + 1,
                ]
            );

            foreach ($unitData['skills'] as $skillPosition => $skillData) {
                $skill = Skill::updateOrCreate(
                    ['unit_id' => $unit->id, 'slug' => $skillData['slug']],
                    [
                        'title' => $skillData['title'],
                        'icon' => $skillData['icon'],
                        'position' => $skillPosition + 1,
                    ]
                );

                foreach ($skillData['lessons'] as $lessonPosition => $lessonData) {
                    $lesson = Lesson::updateOrCreate(
                        ['skill_id' => $skill->id, 'position' => $lessonPosition + 1],
                        ['title' => $lessonData['title'], 'xp_reward' => 10]
                    );

                    foreach ($lessonData['exercises'] as $i => $exercise) {
                        $this->syncExercise($lesson, $i + 1, $exercise);
                    }
                }
            }
        }
    }

    /**
     * @param  array{type:string,prompt:string,correct?:string,options?:array<int,string>,answers?:array<int,string>,tokens?:array<int,string>,pairs?:array<string,string>}  $data
     */
    private function syncExercise(Lesson $lesson, int $position, array $data): void
    {
        $payload = match (true) {
            isset($data['answers']) => ['answers' => $data['answers']],
            isset($data['tokens']) => ['answer' => $data['tokens']],
            isset($data['pairs']) => ['pairs' => $data['pairs']],
            default => null,
        };

        $exercise = Exercise::updateOrCreate(
            ['lesson_id' => $lesson->id, 'position' => $position],
            ['type' => $data['type'], 'prompt' => $data['prompt'], 'payload' => $payload]
        );

        $exercise->options()->delete();

        if (isset($data['correct'], $data['options'])) {
            $options = collect([$data['correct'], ...$data['options']])->shuffle()->values();

            foreach ($options as $i => $text) {
                $exercise->options()->create([
                    'text' => $text,
                    'is_correct' => $text === $data['correct'],
                    'position' => $i + 1,
                ]);
            }
        }
    }

    /**
     * Currículo completo del curso de Laravel.
     */
    private function curriculum(): array
    {
        return [
            // ===================== UNIDAD 1 =====================
            [
                'title' => 'Fundamentos',
                'description' => 'Rutas y consola Artisan.',
                'color' => 'green',
                'skills' => [
                    [
                        'title' => 'Rutas',
                        'slug' => 'rutas',
                        'icon' => '🛣️',
                        'lessons' => [
                            [
                                'title' => 'Tu primera ruta',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `Route::get(\'/posts\', ...)`?',
                                        'correct' => 'Responde a peticiones GET en /posts',
                                        'options' => ['Borra el registro /posts', 'Crea una tabla llamada posts', 'Envía un correo a /posts'],
                                    ],
                                    [
                                        'type' => 'translate',
                                        'prompt' => 'Escribe el verbo HTTP que se usa para CREAR un recurso (en minúsculas).',
                                        'answers' => ['post'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Completa la ruta que responde a GET: `Route::____(\'/posts\', ...);`',
                                        'answers' => ['get'],
                                    ],
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena la ruta que lista los posts.',
                                        'tokens' => ['Route::get(\'/posts\',', 'PostController::class);'],
                                    ],
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada verbo HTTP con lo que hace.',
                                        'pairs' => [
                                            'Route::get' => 'leer',
                                            'Route::post' => 'crear',
                                            'Route::put' => 'actualizar',
                                            'Route::delete' => 'borrar',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Parámetros y nombres',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Para qué sirve `->name(\'posts.index\')` en una ruta?',
                                        'correct' => 'Para darle un nombre y poder usar route(\'posts.index\')',
                                        'options' => ['Para cambiar la URL', 'Para proteger la ruta con contraseña', 'Para cachear la respuesta'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Captura un parámetro en la URL: `Route::get(\'/posts/{____}\', ...);`',
                                        'answers' => ['post', 'id'],
                                    ],
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena la ruta con parámetro.',
                                        'tokens' => ['Route::get(\'/posts/{post}\',', '[PostController::class, \'show\']);'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Artisan',
                        'slug' => 'artisan',
                        'icon' => '⚙️',
                        'lessons' => [
                            [
                                'title' => 'La consola',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Crea un modelo llamado Post: `php artisan make:____ Post`',
                                        'answers' => ['model'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `php artisan migrate`?',
                                        'correct' => 'Ejecuta las migraciones pendientes',
                                        'options' => ['Borra toda la base de datos', 'Crea un usuario admin', 'Compila el CSS'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Levanta el servidor de desarrollo: `php artisan ____`',
                                        'answers' => ['serve'],
                                    ],
                                    [
                                        'type' => 'translate',
                                        'prompt' => 'Escribe el comando para ver TODAS las rutas de la app.',
                                        'answers' => ['php artisan route:list', 'artisan route:list', 'route:list'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 2 =====================
            [
                'title' => 'Eloquent',
                'description' => 'Modelos, consultas y migraciones.',
                'color' => 'blue',
                'skills' => [
                    [
                        'title' => 'Modelos',
                        'slug' => 'modelos',
                        'icon' => '🗄️',
                        'lessons' => [
                            [
                                'title' => 'Modelos y relaciones',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Todo modelo Eloquent extiende de: `class Post extends ____`',
                                        'answers' => ['Model'],
                                    ],
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada relación de Eloquent.',
                                        'pairs' => [
                                            'hasMany' => 'uno a muchos',
                                            'belongsTo' => 'muchos a uno',
                                            'hasOne' => 'uno a uno',
                                            'belongsToMany' => 'muchos a muchos',
                                        ],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué devuelve `Post::find(1)`?',
                                        'correct' => 'El post con id 1, o null si no existe',
                                        'options' => ['Siempre un array', 'Solo el conteo', 'Una excepción si no existe'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `Post::firstOrCreate([\'slug\' => \'x\'])`?',
                                        'correct' => 'Busca por slug y lo crea si no existe',
                                        'options' => ['Borra el primero', 'Actualiza todos', 'Cuenta los posts'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Consultas',
                                'exercises' => [
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena la consulta de los últimos 10 posts.',
                                        'tokens' => ['Post::latest()', '->take(10)', '->get();'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Filtra por columna: `Post::____(\'published\', true)->get();`',
                                        'answers' => ['where'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué devuelve `Post::count()`?',
                                        'correct' => 'El número de posts',
                                        'options' => ['El primer post', 'Un array de posts', 'Un booleano'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Migraciones',
                        'slug' => 'migraciones',
                        'icon' => '📐',
                        'lessons' => [
                            [
                                'title' => 'Definir tablas',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Crea una migración: `php artisan make:____ create_posts_table`',
                                        'answers' => ['migration'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué método crea la tabla dentro de la migración?',
                                        'correct' => 'Schema::create',
                                        'options' => ['Table::make', 'DB::new', 'Model::create'],
                                    ],
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena la línea que añade una columna de texto opcional.',
                                        'tokens' => ['$table->', 'string(\'title\')', '->nullable();'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `$table->foreignId(\'user_id\')->constrained()`?',
                                        'correct' => 'Crea una clave foránea que apunta a users',
                                        'options' => ['Crea un índice de texto', 'Borra la tabla users', 'Añade una columna JSON'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 3 =====================
            [
                'title' => 'Blade',
                'description' => 'Plantillas y componentes.',
                'color' => 'purple',
                'skills' => [
                    [
                        'title' => 'Plantillas',
                        'slug' => 'plantillas',
                        'icon' => '🎨',
                        'lessons' => [
                            [
                                'title' => 'Sintaxis Blade',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Cómo muestras una variable escapada en Blade?',
                                        'correct' => '{{ $name }}',
                                        'options' => ['{{{ name }}}', '<%= name %>', '${name}'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Recorre una colección: `@____($items as $item)`',
                                        'answers' => ['foreach'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Cuál es la directiva para un condicional?',
                                        'correct' => '@if',
                                        'options' => ['@when', '@case', '@switch'],
                                    ],
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada directiva de Blade.',
                                        'pairs' => [
                                            '@extends' => 'heredar layout',
                                            '@include' => 'incluir otra vista',
                                            '@csrf' => 'token de seguridad',
                                            '@foreach' => 'bucle',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Componentes',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Cómo invocas un componente Blade llamado alert?',
                                        'correct' => '<x-alert />',
                                        'options' => ['@alert()', '{{ alert }}', '<blade:alert />'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Pasa una prop al componente: `<x-alert :____="\'error\'" />`',
                                        'answers' => ['type'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 4 =====================
            [
                'title' => 'Livewire',
                'description' => 'Frontend reactivo sin escribir JS.',
                'color' => 'yellow',
                'skills' => [
                    [
                        'title' => 'Reactividad',
                        'slug' => 'reactividad',
                        'icon' => '⚡',
                        'lessons' => [
                            [
                                'title' => 'Directivas Livewire',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `wire:click`?',
                                        'correct' => 'Llama a un método del componente al hacer clic',
                                        'options' => ['Recarga la página', 'Borra el componente', 'Crea una ruta GET'],
                                    ],
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada directiva de Livewire.',
                                        'pairs' => [
                                            'wire:model' => 'two-way binding',
                                            'wire:click' => 'clic → método',
                                            'wire:poll' => 'refresco periódico',
                                            'wire:navigate' => 'navegación tipo SPA',
                                        ],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Crea un componente: `php artisan make:____ Counter`',
                                        'answers' => ['livewire'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Dónde vive el estado de un componente Livewire?',
                                        'correct' => 'En propiedades públicas de la clase PHP',
                                        'options' => ['En localStorage', 'En un archivo JSON', 'En la sesión del navegador'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Acciones y estado',
                                'exercises' => [
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena el método que incrementa el contador.',
                                        'tokens' => ['public function increment()', '{ $this->count++; }'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `$this->reset()`?',
                                        'correct' => 'Devuelve sus propiedades a su valor inicial',
                                        'options' => ['Borra la base de datos', 'Cierra la sesión', 'Recarga el navegador'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Enlaza un input al estado: `<input wire:____="text">`',
                                        'answers' => ['model'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 5 =====================
            [
                'title' => 'Testing',
                'description' => 'Prueba tu aplicación como un profesional.',
                'color' => 'orange',
                'skills' => [
                    [
                        'title' => 'Tests',
                        'slug' => 'tests',
                        'icon' => '🧪',
                        'lessons' => [
                            [
                                'title' => 'Probar tu app',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué comando ejecuta los tests?',
                                        'correct' => 'php artisan test',
                                        'options' => ['php artisan serve', 'php artisan test:run --all', 'composer test-all'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Haz una petición en un test: `$this->____(\'/posts\')->assertOk();`',
                                        'answers' => ['get'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué trait limpia la base de datos entre tests?',
                                        'correct' => 'RefreshDatabase',
                                        'options' => ['CleanDb', 'DatabaseFresh', 'ResetTables'],
                                    ],
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada aserción con su código HTTP.',
                                        'pairs' => [
                                            'assertOk' => '200',
                                            'assertNotFound' => '404',
                                            'assertForbidden' => '403',
                                            'assertRedirect' => 'redirección',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Aserción fina',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué afirma `assertSee(\'Hola\')`?',
                                        'correct' => 'Que la respuesta contiene el texto «Hola»',
                                        'options' => ['Que existe un archivo Hola', 'Que hay un error', 'Que el status es 500'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Afirma un 201 al crear: `->assert____(201);`',
                                        'answers' => ['created'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function seedShop(): void
    {
        $items = [
            ['key' => 'streak_freeze', 'name' => 'Congelador de racha', 'description' => 'Protege tu racha un día.', 'type' => 'streak_freeze', 'price' => 200],
            ['key' => 'heart_refill', 'name' => 'Recarga de corazones', 'description' => 'Rellena tus corazones al máximo.', 'type' => 'heart_refill', 'price' => 350],
            ['key' => 'double_xp', 'name' => 'Doble XP', 'description' => 'Gana el doble de XP por 15 minutos.', 'type' => 'double_xp', 'price' => 100],
        ];

        foreach ($items as $item) {
            ShopItem::firstOrCreate(['key' => $item['key']], $item + ['is_active' => true]);
        }
    }

    private function seedAchievements(): void
    {
        $achievements = [
            ['key' => 'first_lesson', 'name' => 'Primer paso', 'description' => 'Completa tu primera lección.', 'icon' => '🎓', 'threshold' => 1],
            ['key' => 'streak_7', 'name' => 'Constante', 'description' => 'Mantén una racha de 7 días.', 'icon' => '🔥', 'threshold' => 7],
            ['key' => 'xp_1000', 'name' => 'Estudioso', 'description' => 'Acumula 1000 XP.', 'icon' => '⭐', 'threshold' => 1000],
            ['key' => 'crowns_10', 'name' => 'Coronado', 'description' => 'Consigue 10 coronas.', 'icon' => '👑', 'threshold' => 10],
        ];

        foreach ($achievements as $achievement) {
            Achievement::firstOrCreate(['key' => $achievement['key']], $achievement);
        }
    }
}
