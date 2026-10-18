<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Exercise;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Skill;
use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Currículo extra enfocado en el examen técnico de Laravel:
 * unidades 6-12 (PHP, CRUD, validación, relaciones, auth/API, teoría y simulacro)
 * + guías de estudio para TODAS las unidades del curso.
 */
class ExamCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $course = $this->course();

        $titles = array_column($this->curriculum(), 'title');
        $offset = (int) Unit::where('course_id', $course->id)->whereNotIn('title', $titles)->max('position');

        foreach ($this->curriculum() as $unitIndex => $unitData) {
            $unit = Unit::updateOrCreate(
                ['course_id' => $course->id, 'title' => $unitData['title']],
                [
                    'description' => $unitData['description'],
                    'color' => $unitData['color'],
                    'position' => $offset + $unitIndex + 1,
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

        $this->applyGuides($course);
    }

    private function course(): Course
    {
        $es = Language::firstOrCreate(['code' => 'es'], ['name' => 'Español', 'flag' => '🇪🇸']);
        $laravel = Language::firstOrCreate(['code' => 'lv'], ['name' => 'Laravel', 'flag' => '💻']);

        return Course::firstOrCreate(
            ['from_language_id' => $es->id, 'to_language_id' => $laravel->id],
            [
                'title' => 'Laravel',
                'description' => 'Aprende Laravel con ejercicios cortos, como un idioma.',
                'position' => 1,
                'is_active' => true,
            ]
        );
    }

    private function applyGuides(Course $course): void
    {
        foreach ($this->guides() as $title => $guide) {
            Unit::where('course_id', $course->id)
                ->where('title', $title)
                ->update(['guide' => $guide]);
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
     * Unidades 6-12: el temario del examen técnico.
     */
    private function curriculum(): array
    {
        return [
            // ===================== UNIDAD 6 =====================
            [
                'title' => 'PHP esencial',
                'description' => 'Variables, arrays y lógica PHP.',
                'color' => 'green',
                'skills' => [
                    [
                        'title' => 'Datos y arrays',
                        'slug' => 'php-datos',
                        'icon' => '🧩',
                        'lessons' => [
                            [
                                'title' => 'Datos en PHP',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué símbolo va SIEMPRE antes del nombre de una variable en PHP?',
                                        'correct' => '$',
                                        'options' => ['#', '@', '&'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué devuelve `count($items)`?',
                                        'correct' => 'El número de elementos del array',
                                        'options' => ['El primer elemento', 'La suma de los valores', 'El texto "items"'],
                                    ],
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena el foreach que recorre los items e imprime cada uno.',
                                        'tokens' => ['foreach ($items as $item)', '{ echo $item; }'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Cómo accedes al PRIMER elemento del array `$frutas`?',
                                        'correct' => '$frutas[0]',
                                        'options' => ['$frutas[1]', '$frutas.primero', 'first($frutas)'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Strings útiles',
                                'exercises' => [
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada función de texto con lo que hace.',
                                        'pairs' => [
                                            'str_contains' => '¿contiene otro texto?',
                                            'strtoupper' => 'a MAYÚSCULAS',
                                            'implode' => 'array a texto',
                                            'explode' => 'texto a array',
                                        ],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Comprueba si un texto contiene otro: `if (str____($titulo, "Laravel")) { ... }`',
                                        'answers' => ['contains'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `implode(", ", $nombres)`?',
                                        'correct' => 'Une los elementos en un solo texto separados por comas',
                                        'options' => ['Separa el texto por comas', 'Cuenta las comas', 'Convierte el texto a mayúsculas'],
                                    ],
                                    [
                                        'type' => 'translate',
                                        'prompt' => 'Escribe la función que convierte un texto a MAYÚSCULAS.',
                                        'answers' => ['strtoupper', 'strtoupper()'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Lógica y funciones',
                        'slug' => 'php-logica',
                        'icon' => '🔀',
                        'lessons' => [
                            [
                                'title' => 'Condiciones',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué devuelve `$estado ?? "pendiente"` si $estado es null?',
                                        'correct' => 'El texto "pendiente"',
                                        'options' => ['null', 'Un error', 'El texto "estado"'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué operador da un valor por defecto cuando la variable es null?',
                                        'correct' => '??',
                                        'options' => ['=>', '++', '::'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `match($estado) { "ok" => 1, default => 0 }`?',
                                        'correct' => 'Devuelve 1 si el estado es "ok" y 0 en cualquier otro caso',
                                        'options' => ['Compara HTML', 'Lanza un error si no hay coincidencia', 'Devuelve siempre 0'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué operador compara valor Y tipo a la vez?',
                                        'correct' => '===',
                                        'options' => ['==', '=', '!='],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Funciones',
                                'exercises' => [
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena la función que suma dos números.',
                                        'tokens' => ['function sumar($a, $b)', '{ return $a + $b; }'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Devuelve el resultado desde una función PHP: `____ $total;`',
                                        'answers' => ['return'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué es `fn ($n) => $n * 2`?',
                                        'correct' => 'Una función flecha (arrow function)',
                                        'options' => ['Un bucle', 'Un array asociativo', 'Una clase'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 7 =====================
            [
                'title' => 'El CRUD del examen',
                'description' => 'Los 7 métodos del resource controller.',
                'color' => 'blue',
                'skills' => [
                    [
                        'title' => 'Las 7 acciones',
                        'slug' => 'crud-rutas',
                        'icon' => '🚦',
                        'lessons' => [
                            [
                                'title' => 'El resource controller',
                                'exercises' => [
                                    [
                                        'type' => 'translate',
                                        'prompt' => 'Escribe el método del controller que LISTA todos los registros (en minúsculas).',
                                        'answers' => ['index'],
                                    ],
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada método del CRUD con lo que hace.',
                                        'pairs' => [
                                            'index' => 'listar',
                                            'create' => 'formulario nuevo',
                                            'store' => 'guardar',
                                            'edit' => 'formulario de edición',
                                            'update' => 'actualizar',
                                            'destroy' => 'eliminar',
                                        ],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Muestra el detalle de UN registro: método `____()`',
                                        'answers' => ['show'],
                                    ],
                                    [
                                        'type' => 'translate',
                                        'prompt' => 'Escribe el comando que lista TODAS las rutas de la app.',
                                        'answers' => ['php artisan route:list', 'artisan route:list', 'route:list', 'php artisan route list'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Generar el resource',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Genera el controller con las 7 acciones: `php artisan make:controller LicenciaController --____`',
                                        'answers' => ['resource'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué línea registra las 7 rutas del CRUD de licencias?',
                                        'correct' => "Route::resource('licencias', LicenciaController::class);",
                                        'options' => ["Route::crud('licencias');", "Route::all('licencias');", "Route::get('licencias', 'todo');"],
                                    ],
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena la línea del route resource.',
                                        'tokens' => ['Route::resource(', "'licencias',", 'LicenciaController::class);'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'index() y formularios',
                        'slug' => 'crud-index',
                        'icon' => '📋',
                        'lessons' => [
                            [
                                'title' => 'Listar con filtro',
                                'exercises' => [
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena el listado con paginación.',
                                        'tokens' => ['$licencias = Licencia::latest()', '->paginate(10);'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué diferencia hay entre `get()` y `paginate(10)`?',
                                        'correct' => 'paginate divide el resultado en páginas de 10 y genera los enlaces',
                                        'options' => ['get() es más rápido y paginado', 'paginate devuelve solo 10 registros sin páginas', 'No hay diferencia'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Lee el parámetro de filtro de la URL: `$request->____("estado")`',
                                        'answers' => ['query', 'input'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `->when($request->filled("estado"), fn ($q) => $q->where("estado", $request->estado))`?',
                                        'correct' => 'Aplica el filtro solo si el campo viene con valor',
                                        'options' => ['Aplica siempre el filtro', 'Lanza un error si falta el campo', 'Ignora el parámetro estado'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'store() y update()',
                                'exercises' => [
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena el store(): validar, crear y redirigir.',
                                        'tokens' => ['$datos = $request->validate([...]);', 'Licencia::create($datos);', 'return redirect()->route("licencias.index");'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Crea el registro con los datos ya validados: `Licencia::____($datos);`',
                                        'answers' => ['create'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `redirect()->route("licencias.index")->with("exito", "Licencia radicada")`?',
                                        'correct' => 'Redirige al listado guardando un mensaje flash "exito"',
                                        'options' => ['Muestra una alerta JavaScript', 'Borra el mensaje anterior', 'Envía un correo'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Actualiza el registro existente: `$licencia->____($datos);`',
                                        'answers' => ['update'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'destroy() y detalles',
                        'slug' => 'crud-destroy',
                        'icon' => '🗑️',
                        'lessons' => [
                            [
                                'title' => 'Eliminar y ver',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Elimina el registro dentro de destroy(): `$licencia->____();`',
                                        'answers' => ['delete'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace el route model binding `show(Licencia $licencia)`?',
                                        'correct' => 'Laravel busca la licencia por el id de la URL y lanza 404 si no existe',
                                        'options' => ['Hay que buscarla manualmente con find()', 'Solo funciona en GET', 'Crea la licencia si no existe'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'En Blade, un formulario de borrado necesita el método: `@____("DELETE")`',
                                        'answers' => ['method'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => 'Tras `->with("exito", "Borrada")`, ¿cómo se muestra el mensaje en el listado?',
                                        'correct' => 'session("exito")',
                                        'options' => ['request("exito")', 'flash("exito")', '$exito'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 8 =====================
            [
                'title' => 'Validación a fondo',
                'description' => 'Reglas, unique al editar y CSRF.',
                'color' => 'purple',
                'skills' => [
                    [
                        'title' => 'Reglas del examen',
                        'slug' => 'val-reglas',
                        'icon' => '✅',
                        'lessons' => [
                            [
                                'title' => 'validate()',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Valida los datos de la petición antes de guardar: `$request->____([...]);`',
                                        'answers' => ['validate'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué exige la regla `required|string|max:255`?',
                                        'correct' => 'Obligatorio, texto y máximo 255 caracteres',
                                        'options' => ['Opcional y numérico', 'Mínimo 255 caracteres', 'Solo letras'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'El número no puede repetirse: `"numero" => "required|____:licencias,numero"`',
                                        'answers' => ['unique'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Solo permite estos valores: `"estado" => "in:radicada,____,aprobada,rechazada"`',
                                        'answers' => ['en_revision'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Errores en el formulario',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Muestra el error de un campo: `@____("numero") <span>{{ $message }}</span> @enderror`',
                                        'answers' => ['error'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `old("direccion")` dentro de un input?',
                                        'correct' => 'Recupera el valor anterior para no borrarlo tras un error',
                                        'options' => ['Muestra un aviso', 'Borra el campo', 'Duplica el input'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Token de seguridad obligatorio en todo formulario POST: `@____`',
                                        'answers' => ['csrf'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué significa el error 419 Page Expired?',
                                        'correct' => 'El token CSRF falta o expiró',
                                        'options' => ['La ruta no existe', 'El usuario no está logueado', 'El servidor cayó'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Form Requests',
                        'slug' => 'val-requests',
                        'icon' => '🛡️',
                        'lessons' => [
                            [
                                'title' => 'FormRequest paso a paso',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Genera el form request: `php artisan make:request ____LicenciaRequest`',
                                        'answers' => ['store'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Dónde van las reglas en un Form Request?',
                                        'correct' => 'En el método rules()',
                                        'options' => ['En boot()', 'En el modelo', 'En la migración'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Para qué sirve authorize()?',
                                        'correct' => 'Devolver true o false para permitir la petición',
                                        'options' => ['Firmar el token', 'Definir columnas', 'Validar emails'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'unique al editar',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Al editar, ignora el propio registro: `unique:licencias,numero,____`',
                                        'answers' => ['$licencia->id', '$this->licencia->id', 'licenciaid', 'thislicenciaid'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué pasa si en update NO ignoras el id en unique?',
                                        'correct' => 'Da error "ya en uso" aunque no hayas cambiado ese campo',
                                        'options' => ['Nada', 'Se borra el registro', 'Se duplica'],
                                    ],
                                    [
                                        'type' => 'translate',
                                        'prompt' => 'Escribe la regla que hace un campo opcional.',
                                        'answers' => ['nullable'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 9 =====================
            [
                'title' => 'Relaciones Eloquent',
                'description' => 'hasMany, belongsTo, with() y N+1.',
                'color' => 'yellow',
                'skills' => [
                    [
                        'title' => 'Uno a muchos',
                        'slug' => 'rel-uno-muchos',
                        'icon' => '🔗',
                        'lessons' => [
                            [
                                'title' => 'hasMany / belongsTo',
                                'exercises' => [
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada relación con su caso.',
                                        'pairs' => [
                                            'hasMany' => 'una licencia, muchos documentos',
                                            'belongsTo' => 'un documento, una licencia',
                                            'hasOne' => 'un usuario, un perfil',
                                            'belongsToMany' => 'un rol, muchos permisos',
                                        ],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué columna espera hasMany en la tabla hija por convención?',
                                        'correct' => 'licencia_id',
                                        'options' => ['documento_id', 'id_licencia', 'hasmany_id'],
                                    ],
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena el método de la relación.',
                                        'tokens' => ['public function documentos()', '{ return $this->hasMany(Documento::class); }'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Cómo accedes a los documentos de una licencia?',
                                        'correct' => '$licencia->documentos',
                                        'options' => ['$licencia::documentos', 'documentos($licencia)', '$licencia["documentos"]'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'with() y el problema N+1',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Carga la relación de golpe para evitar el N+1: `Licencia::____("documentos")->get();`',
                                        'answers' => ['with'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué es el problema N+1?',
                                        'correct' => 'Una consulta extra por cada registro al usar su relación dentro de un bucle',
                                        'options' => ['Un error de validación', 'Una tabla con N columnas', 'Un test que falla'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Cuenta los documentos sin cargarlos: `Licencia::____("documentos")->get();`',
                                        'answers' => ['withCount'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Filtros con relaciones',
                        'slug' => 'rel-filtros',
                        'icon' => '🔎',
                        'lessons' => [
                            [
                                'title' => 'whereHas',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Solo las licencias que TIENEN documentos: `Licencia::____("documentos")->get();`',
                                        'answers' => ['has'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `whereHas("documentos", fn ($q) => $q->where("aprobado", true))`?',
                                        'correct' => 'Licencias con al menos un documento aprobado',
                                        'options' => ['Todas las licencias', 'Solo la primera licencia', 'Documentos sin licencia'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Solo las licencias SIN documentos: `Licencia::____("documentos")->get();`',
                                        'answers' => ['doesntHave'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Fechas y orden',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Ordena de la más nueva a la más vieja: `Licencia::____()->get();`',
                                        'answers' => ['latest'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `whereDate("fecha_solicitud", today())`?',
                                        'correct' => 'Filtra las solicitudes de hoy',
                                        'options' => ['Ordena por fecha', 'Formatea la fecha', 'Cuenta las fechas'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Cuenta todas las licencias: `Licencia::____();`',
                                        'answers' => ['count'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 10 =====================
            [
                'title' => 'Auth y API',
                'description' => 'Middleware, login y Sanctum.',
                'color' => 'orange',
                'skills' => [
                    [
                        'title' => 'Protege la app',
                        'slug' => 'auth-sesion',
                        'icon' => '🔐',
                        'lessons' => [
                            [
                                'title' => 'Middleware auth',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué hace `->middleware("auth")` en una ruta?',
                                        'correct' => 'Solo usuarios con sesión entran; si no, redirige al login',
                                        'options' => ['Cifra la respuesta', 'Hace pública la ruta', 'Borra la sesión'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Agrupa rutas protegidas: `Route::____("auth")->group(...)`',
                                        'answers' => ['middleware'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Obtén el usuario autenticado: `auth()->____()`',
                                        'answers' => ['user'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué devuelve `Auth::check()`?',
                                        'correct' => 'true si hay sesión iniciada',
                                        'options' => ['El email del usuario', 'El id de la sesión', 'Un error si no hay login'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Login y logout',
                                'exercises' => [
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena el intento de login.',
                                        'tokens' => ['if (Auth::attempt($credenciales))', '{ return redirect()->intended("dashboard"); }'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Cierra la sesión: `Auth::____();`',
                                        'answers' => ['logout'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Cómo se guardan las contraseñas en Laravel?',
                                        'correct' => 'Hasheadas (bcrypt), nunca en texto plano',
                                        'options' => ['En texto plano para poder leerlas', 'Cifradas y visibles en la BD', 'En un archivo .env'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'API con Sanctum',
                        'slug' => 'api-sanctum',
                        'icon' => '📡',
                        'lessons' => [
                            [
                                'title' => 'Rutas API',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Dónde van las rutas de la API?',
                                        'correct' => 'En routes/api.php, con el prefijo /api',
                                        'options' => ['En routes/web.php', 'En el controller', 'En bootstrap/app.php'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Registra el CRUD de la API: `Route::____("licencias", LicenciaController::class);`',
                                        'answers' => ['apiResource'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué devuelve normalmente un endpoint de API?',
                                        'correct' => 'JSON con response()->json([...])',
                                        'options' => ['Una vista Blade', 'Una redirección', 'HTML plano'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Tokens Sanctum',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Crea un token para el usuario: `$user->____("app")->plainTextToken;`',
                                        'answers' => ['createToken'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué middleware protege una ruta de API con Sanctum?',
                                        'correct' => 'auth:sanctum',
                                        'options' => ['auth:api', 'csrf', 'throttle'],
                                    ],
                                    [
                                        'type' => 'translate',
                                        'prompt' => 'Escribe la palabra que acompaña al token en el header Authorization.',
                                        'answers' => ['bearer'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 11 =====================
            [
                'title' => 'Teoría y trampas',
                'description' => 'MVC, códigos HTTP y errores clásicos.',
                'color' => 'green',
                'skills' => [
                    [
                        'title' => 'Conceptos de examen',
                        'slug' => 'teoria-conceptos',
                        'icon' => '🧠',
                        'lessons' => [
                            [
                                'title' => 'MVC y Laravel',
                                'exercises' => [
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada capa con su papel.',
                                        'pairs' => [
                                            'Modelo' => 'datos y base de datos',
                                            'Vista' => 'lo que ve el usuario',
                                            'Controlador' => 'lógica entre modelo y vista',
                                            'Ruta' => 'URL a controlador',
                                        ],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Dónde debe ir la lógica de consultas complejas?',
                                        'correct' => 'En el modelo o en servicios, no en la vista',
                                        'options' => ['En la vista Blade', 'En la migración', 'En el archivo .env'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué es Artisan?',
                                        'correct' => 'La consola de comandos de Laravel',
                                        'options' => ['Un ORM', 'Un motor de plantillas', 'Un servidor web'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Eloquent vs Query Builder',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Cuál es la diferencia principal?',
                                        'correct' => 'Eloquent usa modelos con relaciones; Query Builder usa DB::table sin modelos',
                                        'options' => ['Eloquent es SQL puro', 'No hay diferencia', 'Query Builder no puede filtrar'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Consulta sin modelo: `DB::____("licencias")->get();`',
                                        'answers' => ['table'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Cuál permite route model binding y relaciones?',
                                        'correct' => 'Eloquent',
                                        'options' => ['Query Builder', 'La fachada DB', 'Schema'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Errores clásicos',
                        'slug' => 'teoria-errores',
                        'icon' => '💥',
                        'lessons' => [
                            [
                                'title' => 'Códigos HTTP',
                                'exercises' => [
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Empareja cada código con su significado.',
                                        'pairs' => [
                                            '419' => 'token CSRF expirado',
                                            '404' => 'no encontrado',
                                            '403' => 'prohibido',
                                            '405' => 'método no permitido',
                                            '422' => 'validación fallida',
                                        ],
                                    ],
                                    [
                                        'type' => 'translate',
                                        'prompt' => 'Escribe el código del error "no encontrado".',
                                        'answers' => ['404'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué significa 405 Method Not Allowed?',
                                        'correct' => 'La ruta existe pero no acepta ese verbo HTTP',
                                        'options' => ['Falta el token CSRF', 'El usuario no tiene permiso', 'El servidor falló'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué significa 422 en una API?',
                                        'correct' => 'Errores de validación',
                                        'options' => ['No autorizado', 'Servidor caído', 'Página movida'],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Trampas típicas',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => 'Ves el error "Add [numero] to fillable property". ¿Qué falta?',
                                        'correct' => 'El campo numero en $fillable del modelo',
                                        'options' => ['Una migración nueva', 'Un @csrf', 'Un índice de la tabla'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Muestra todas las rutas en consola: `php artisan ____`',
                                        'answers' => ['route:list', 'route list'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Por qué un formulario POST da error 419?',
                                        'correct' => 'Falta @csrf en el formulario',
                                        'options' => ['El modelo no existe', 'La base de datos está caída', 'El método está mal escrito'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Inspecciona una variable y detiene la ejecución: `____($licencia);`',
                                        'answers' => ['dd'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ===================== UNIDAD 12 =====================
            [
                'title' => 'Simulacro final',
                'description' => 'Rondas mixtas con el flujo del examen.',
                'color' => 'blue',
                'skills' => [
                    [
                        'title' => 'Repaso integrador',
                        'slug' => 'simulacro',
                        'icon' => '🏁',
                        'lessons' => [
                            [
                                'title' => 'Mini-simulacro 1',
                                'exercises' => [
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Migración de licencias: columna de fecha de solicitud: `$table->____("fecha_solicitud");`',
                                        'answers' => ['date'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'El radicado se guarda como texto corto: `$table->____("numero");`',
                                        'answers' => ['string'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué regla impide números de radicado duplicados?',
                                        'correct' => 'unique:licencias,numero',
                                        'options' => ['required:numero', 'exists:numero', 'same:numero'],
                                    ],
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena el update del examen.',
                                        'tokens' => ['$licencia->update($datos);', 'return redirect()->route("licencias.index");'],
                                    ],
                                    [
                                        'type' => 'match_pairs',
                                        'prompt' => 'Calentamiento: empareja la acción con su método.',
                                        'pairs' => [
                                            'ver formulario nuevo' => 'create',
                                            'guardar' => 'store',
                                            'listar' => 'index',
                                            'eliminar' => 'destroy',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Mini-simulacro 2',
                                'exercises' => [
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => 'En el formulario de edición, ¿de dónde sale el valor actual del campo?',
                                        'correct' => 'Del modelo: old("campo", $licencia->campo)',
                                        'options' => ['Solo de old()', 'De la sesión', 'Del archivo .env'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Crea la migración del examen: `php artisan make:____ create_licencias_table`',
                                        'answers' => ['migration'],
                                    ],
                                    [
                                        'type' => 'order_words',
                                        'prompt' => 'Ordena el index con filtro y paginación.',
                                        'tokens' => ['$licencias = Licencia::when($request->filled("estado"),', 'fn ($q) => $q->where("estado", $request->estado))', '->paginate(10);'],
                                    ],
                                    [
                                        'type' => 'fill_blank',
                                        'prompt' => 'Cuenta las licencias creadas: `Licencia::____();`',
                                        'answers' => ['count'],
                                    ],
                                    [
                                        'type' => 'select_translation',
                                        'prompt' => '¿Qué muestra `php artisan route:list`?',
                                        'correct' => 'Todas las rutas de la app con verbo, URI y acción',
                                        'options' => ['Las tablas de la base de datos', 'Los modelos creados', 'Los errores del log'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Guías de estudio por unidad (se muestran en el botón "Guía" del camino).
     *
     * @return array<string, array{intro:string,points:array<int,string>,code:string,traps:array<int,string>}>
     */
    private function guides(): array
    {
        return [
            'Fundamentos' => [
                'intro' => 'La base del examen: definir rutas y ejecutar comandos de Artisan sin dudar.',
                'points' => [
                    'Cada verbo HTTP tiene su método: Route::get (leer), post (crear), put/patch (actualizar), delete (borrar).',
                    'Nombra las rutas con ->name("posts.index") para poder usar route("posts.index").',
                    'Los parámetros de la URL se capturan entre llaves: /posts/{post}.',
                    'php artisan serve levanta el servidor; migrate ejecuta migraciones; route:list muestra las rutas.',
                ],
                'code' => <<<'CODE'
Route::get('/licencias', [LicenciaController::class, 'index'])
    ->name('licencias.index');
CODE,
                'traps' => [
                    'GET lee, POST crea, PUT/PATCH actualiza, DELETE borra. Confundirlos cuesta puntos seguros.',
                    'Antes de entregar corre php artisan route:list y verifica que tus rutas existan.',
                    'Si una ruta no aparece en route:list, el problema está en routes/web.php, no en el controller.',
                ],
            ],
            'Eloquent' => [
                'intro' => 'Los modelos son el corazón del examen: consultar, crear y filtrar con Eloquent.',
                'points' => [
                    'Todo modelo extiende Model y protege sus campos con $fillable.',
                    'find() devuelve null si no existe; findOrFail() lanza 404 automáticamente.',
                    'Filtra con where(), ordena con latest()/oldest() y cuenta con count().',
                    'firstOrCreate() busca o crea en un solo paso.',
                ],
                'code' => <<<'CODE'
$licencias = Licencia::where('estado', 'aprobada')
    ->latest()
    ->take(10)
    ->get();
CODE,
                'traps' => [
                    'all() trae TODO sin paginar; en el examen usa paginate(10).',
                    'Si create() falla con "Add [x] to fillable property", agrega el campo a $fillable.',
                    'latest() ordena por created_at descendente, no por id.',
                ],
            ],
            'Blade' => [
                'intro' => 'Las vistas del examen se escriben en Blade: corto, seguro y con directivas.',
                'points' => [
                    '{{ $variable }} muestra escapado (seguro); {!! !!} sin escapar (peligroso).',
                    'Condicionales con @if/@else/@endif y bucles con @foreach/@endforeach.',
                    'Componentes con <x-alert />; layouts con @extends/@section.',
                    'Todo formulario POST lleva @csrf y, si es edición o borrado, @method.',
                ],
                'code' => <<<'CODE'
@foreach ($licencias as $licencia)
    <tr><td>{{ $licencia->numero }}</td></tr>
@endforeach
CODE,
                'traps' => [
                    'No cerrar @section o @endforeach rompe la vista completa.',
                    'Olvidar @csrf = error 419 apenas envíes el formulario.',
                    'En inputs de edición usa value="{{ old(\'campo\', $licencia->campo) }}" para no perder datos.',
                ],
            ],
            'Livewire' => [
                'intro' => 'La app usa Livewire: estado en PHP y reactividad sin JavaScript manual.',
                'points' => [
                    'El estado vive en propiedades PÚBLICAS de la clase del componente.',
                    'wire:model="texto" enlaza un input; wire:click="metodo" llama a un método.',
                    'make:livewire Contador crea un componente nuevo.',
                    '$this->reset() devuelve las propiedades a su valor inicial.',
                ],
                'code' => <<<'CODE'
public $texto = '';

public function guardar(): void
{
    // $this->texto está disponible aquí
}
CODE,
                'traps' => [
                    'Si el binding no actualiza al teclear, falta .live: wire:model.live.',
                    'Los métodos que llama la vista deben ser públicos.',
                    'No mezcles lógica de base de datos en la vista: va en el componente o en el modelo.',
                ],
            ],
            'Testing' => [
                'intro' => 'Saber correr y leer un test te da puntos fáciles en el examen.',
                'points' => [
                    'php artisan test ejecuta toda la suite.',
                    'RefreshDatabase limpia la base de datos entre tests.',
                    '$this->get("/posts")->assertOk() prueba una página; assertSee("texto") busca contenido.',
                    'assertCreated() espera un 201; assertRedirect() espera una redirección.',
                ],
                'code' => <<<'CODE'
$this->post('/licencias', $datos)
    ->assertRedirect('/licencias');
CODE,
                'traps' => [
                    'Sin RefreshDatabase los tests se contaminan con datos reales.',
                    'assertOk equivale a 200; para redirecciones usa assertRedirect.',
                    'Los tests van en tests/Feature (flujo completo) o tests/Unit (aislado).',
                ],
            ],
            'PHP esencial' => [
                'intro' => 'PHP puro: variables, arrays, condiciones y funciones. La base de todo lo demás.',
                'points' => [
                    'Las variables llevan $ delante: $nombre = "Santiago";',
                    'Arrays con []; count() cuenta elementos y foreach los recorre.',
                    'El operador ?? da un valor por defecto si la variable es null.',
                    'Compara siempre con === (valor y tipo).',
                    'Texto: str_contains() busca, strtoupper() pone en mayúsculas, implode() une y explode() separa.',
                ],
                'code' => <<<'CODE'
$estado = $licencia->estado ?? 'pendiente';

if (str_contains($titulo, 'Laravel')) {
    // el texto contiene "Laravel"
}
CODE,
                'traps' => [
                    'count() cuenta elementos de arrays, no caracteres de un texto (para eso strlen()).',
                    'El operador ?? solo reemplaza null, no cadenas vacías.',
                    'Una función sin return devuelve null: revisa siempre tus funciones.',
                ],
            ],
            'El CRUD del examen' => [
                'intro' => 'ESTE es el tema central del examen: el CRUD completo de licencias urbanísticas.',
                'points' => [
                    '7 métodos: index (listar), create (form nuevo), store (guardar), show (detalle), edit (form edición), update (actualizar), destroy (eliminar).',
                    'Genera todo con make:controller LicenciaController --resource y Route::resource("licencias", LicenciaController::class).',
                    'index: Licencia::latest()->paginate(10) y filtro con ->when($request->filled("estado"), ...).',
                    'store/update: primero $datos = $request->validate([...]), luego create($datos) o update($datos).',
                    'Redirige siempre con mensaje flash: ->with("exito", "...") y muéstralo con session("exito").',
                    'El binding Licencia $licencia resuelve el id de la URL automáticamente (404 si no existe).',
                ],
                'code' => <<<'CODE'
public function store(Request $request)
{
    $datos = $request->validate([
        'numero' => 'required|unique:licencias,numero',
        'estado' => 'required|in:radicada,en_revision,aprobada,rechazada',
    ]);

    Licencia::create($datos);

    return redirect()->route('licencias.index')
        ->with('exito', 'Licencia radicada');
}
CODE,
                'traps' => [
                    'create (Eloquent) inserta; store (controller) orquesta. No confundas los nombres.',
                    'Nunca uses $request->all() directo: valida siempre antes.',
                    'En el formulario de borrado: @method("DELETE") o destroy nunca se ejecuta.',
                    'Si el índice filtra, agrega ->withQueryString() a la paginación para no perder el filtro al cambiar de página.',
                ],
            ],
            'Validación a fondo' => [
                'intro' => 'La validación cae SIEMPRE en el examen. Domina reglas, mensajes y CSRF.',
                'points' => [
                    '$request->validate([...]) valida y redirige con los errores automáticamente.',
                    'Reglas clave: required, string, max:255, date, in:a,b,c, unique:tabla,columna, nullable.',
                    'Al editar, ignora el propio registro: unique:licencias,numero, seguido del id.',
                    'En Blade: @error("campo") y old("campo") para mostrar errores sin perder los datos.',
                    'Form Request: make:request, reglas en rules() y permiso en authorize().',
                ],
                'code' => <<<'CODE'
'numero' => 'required|string|max:20|unique:licencias,numero,' . $licencia->id,
'estado' => 'required|in:radicada,en_revision,aprobada,rechazada',
'observaciones' => 'nullable|string',
CODE,
                'traps' => [
                    '419 Page Expired = falta @csrf en el formulario.',
                    'Si unique no ignora el id al editar, da error aunque no cambies el campo.',
                    'Los mensajes se personalizan en lang/es/validation.php.',
                ],
            ],
            'Relaciones Eloquent' => [
                'intro' => 'Relaciones y el clásico problema N+1: puntos seguros si los entiendes.',
                'points' => [
                    'hasMany (una licencia, muchos documentos) y belongsTo (al revés), foránea licencia_id por convención.',
                    'Accede con propiedad ($licencia->documentos) o encadena ($licencia->documentos()->create([...])).',
                    'with("documentos") carga todo de una vez y evita el N+1.',
                    'Filtra según la relación: has(), whereHas(..., fn ($q) => ...), doesntHave().',
                    'withCount("documentos") agrega $licencia->documentos_count.',
                ],
                'code' => <<<'CODE'
$licencias = Licencia::with('documentos')
    ->withCount('documentos')
    ->paginate(10);
CODE,
                'traps' => [
                    'El N+1 es usar la relación dentro de un foreach sin with(): una consulta por fila.',
                    'La foránea sigue la convención singular_id (licencia_id), no id_licencia.',
                    '$licencia->documentos (propiedad) trae la colección; ->documentos() (método) es para seguir consultando.',
                ],
            ],
            'Auth y API' => [
                'intro' => 'Proteger rutas y montar una API con Sanctum: peso medio en el plan de estudio.',
                'points' => [
                    'middleware("auth") protege rutas; auth()->user() da el usuario actual.',
                    'Route::middleware("auth")->group(...) agrupa lo protegido.',
                    'La API va en routes/api.php con prefijo /api y Route::apiResource.',
                    'Los endpoints devuelven JSON: response()->json([...]).',
                    'Sanctum: $user->createToken("app")->plainTextToken y middleware auth:sanctum.',
                ],
                'code' => <<<'CODE'
Route::middleware('auth')->group(function () {
    Route::resource('licencias', LicenciaController::class);
});
CODE,
                'traps' => [
                    'En API el middleware es auth:sanctum, no auth a secas.',
                    'El token viaja en el header Authorization: Bearer {token}.',
                    'Las contraseñas nunca en texto plano: Hash::make() o bcrypt().',
                ],
            ],
            'Teoría y trampas' => [
                'intro' => 'Preguntas de teoría y errores clásicos: memoriza códigos y causas.',
                'points' => [
                    'MVC: Modelo (datos), Vista (interfaz), Controlador (lógica). Las consultas complejas van en el modelo.',
                    'Eloquent (modelos y relaciones) vs Query Builder (DB::table, sin modelos).',
                    'Códigos: 419 token expirado, 403 prohibido, 404 no encontrado, 405 método no permitido, 422 validación fallida, 500 error del servidor.',
                ],
                'code' => <<<'CODE'
// Sin modelo, con Query Builder:
DB::table('licencias')->where('estado', 'aprobada')->get();
CODE,
                'traps' => [
                    '"Add [campo] to fillable property" significa que falta el campo en $fillable.',
                    '405 = la ruta existe pero no acepta ese verbo (ej: POST a una ruta GET).',
                    'Depura con dd($variable) y revisa storage/logs/laravel.log.',
                ],
            ],
            'Simulacro final' => [
                'intro' => 'Checklist del flujo completo del examen. Repite este orden en tu práctica.',
                'points' => [
                    '1) Migración con las columnas EXACTAS del enunciado: numero, solicitante, direccion, tipo, estado, fecha_solicitud, observaciones.',
                    '2) Modelo con $fillable completo.',
                    '3) Route::resource("licencias", LicenciaController::class).',
                    '4) Controller: index con filtro + paginate, store/update con validate, destroy con delete.',
                    '5) Vistas: formularios con @csrf, @method y @error.',
                    '6) Mensajes flash con ->with("exito", ...) mostrados con session("exito").',
                ],
                'code' => <<<'CODE'
php artisan make:migration create_licencias_table
php artisan make:model Licencia -mcr
php artisan route:list
CODE,
                'traps' => [
                    'Lee DOS veces el enunciado: los nombres de campos son puntos literales.',
                    'No renombres columnas por que se vean "bonitas": exactitud y velocidad valen más.',
                    'Administra el tiempo (210 minutos): primero el CRUD funcionando, los detalles al final.',
                ],
            ],
        ];
    }
}
