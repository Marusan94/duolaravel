# SPEC — Clon de Duolingo en Laravel

> Base: `C:\Users\USUARIO\Desktop\laravel-prep` (Laravel 13.37, PHP 8.4, SQLite migrada).
> Estado: **implementado el núcleo**. Pendiente reconciliar detalles con REA (`duolingo-re/ARQUITECTURA-DUOLINGO.md`) cuando se capture la API.
> Alcance: **todo el producto** (núcleo de aprendizaje + gamificación + social + ligas + tienda).
> Verificación: `php artisan test` → **40 passed / 127 assertions**; `npm run build` → OK; `artisan schedule:list` → 2 tareas activas.

## 1. Stack propuesto
- **Backend**: Laravel 13 (PHP 8.4), Eloquent, colas (jobs) para regen de corazones/cierre de ligas, scheduler para tareas diarias/semanales.
- **DB**: SQLite en dev; MySQL/Postgres en prod (columnas JSON para payload de ejercicios).
- **Auth**: Laravel Breeze/Fortify (sesiones) o Sanctum si luego hay API móvil.
- **Frontend**: **Inertia + React + TypeScript + Tailwind** (el usuario ya domina React/TS) — alternativa: Blade + Livewire si se quiere lógica 100% PHP para aprender Laravel.

## 2. Módulos y entidades (migraciones)
- **Auth/Perfil**: `users`, `profiles`.
- **Contenido**: `languages`, `courses`, `units`, `skills`, `lessons`, `exercises`, `exercise_options`.
- **Progreso**: `user_courses`, `user_skill_progress`, `user_lessons`, `xp_events`, `streaks`, `hearts`.
- **Economía**: `gems_wallet`, `gem_transactions`, `shop_items`, `user_items`.
- **Competencia**: `leagues`, `league_members`, `leaderboard_snapshots`.
- **Social**: `friendships`, `activity_feed`, `achievements`, `user_achievements`, `notifications`.

## 3. Motor de ejercicios (patrón Strategy)
- `ExerciseType` (enum) + handler por tipo. Métodos: `prompt()`, `validate(answer): Result`, `xp()`.
- Tipos iniciales: `translate`, `match_pairs`, `select_image`, `order_words`, `listen_type`, `fill_blank`, `type_translation`.
- `LessonRunner` (servicio): arma cola, valida, emite feedback, aplica XP/hearts, cierra lección.

## 4. Servicios de dominio
- `GamificationService`: `awardXp()`, `updateStreak()`, `spendGems()`, `loseHeart()`, `regenerateHearts()`.
- `LeagueService`: `assignLeague()`, `snapshotWeek()`, `promoteDemote()` (job semanal).
- `ShopService`: compra/consumo de items (streak_freeze, double_xp, heart_refill).
- `FeedService`: registrar eventos sociales tras hitos.

## 5. Tareas programadas / colas
- Job: regenerar corazones (cada 30 min o por usuario).
- Scheduler: cierre semanal de ligas (domingo), recálculo de leaderboards, notificaciones de racha.

## 6. Plan de implementación por fases
1. Scaffolding + auth + perfiles.
2. Contenido (modelos + seeders de un curso de ejemplo, p. ej. ES→EN).
3. Motor de ejercicios + runner de lección (1 tipo end-to-end, luego el resto).
4. Progreso/XP/racha/corazones.
5. Tienda + economía de gemas.
6. Ligas + leaderboard.
7. Social + feed + notificaciones.
8. Pulido visual (Tailwind) + pruebas.

## 7. Criterios de aceptación (núcleo)
- Un usuario puede: registrarse, elegir curso, completar una lección, ganar XP, mantener racha, gastar gemas, ver su posición en liga y tener amigos.
- Verificación: `php artisan test` verde + `npm run build` OK.

## 8. Decisiones abiertas (requieren confirmación del usuario)
- ~~¿Frontend **Inertia+React** o **Blade+Livewire**?~~ → **RESUELTO: Blade + Livewire + Tailwind** (elección del agente; el usuario no tenía preferencia).
- ¿Replicar fielmente la API de Duolingo (requiere captura HAR/Fiddler) o diseñar API propia equivalente? → **pendiente de captura HAR/REA**.
- ¿Idioma del curso semilla y conjunto de ejercicios inicial? → **RESUELTO provisional: ES→EN** (contenido semilla).

## 9. Estado de implementación (as-built)
| Módulo | Estado | Artefactos |
|---|---|---|
| Scaffolding + auth | ✅ | Breeze v2.4.2; `/login`, `/register`, `/dashboard` |
| Contenido | ✅ | 7 tablas + seeders (ES→EN, 2 unidades, 3 skills, 3 lecciones, 12 ejercicios) |
| Motor de ejercicios | ✅ | `ExerciseEngine` (select/translate/order/match) + `LessonRunner` |
| Progreso/XP/racha/corazones | ✅ | `GamificationService`, tablas `xp_events`/`streaks`/`hearts` |
| Tienda + gemas | ✅ | `ShopService`, `GemsWallet`, `GemTransaction`, `/shop` |
| Ligas + leaderboard | ✅ | `LeagueService`, `League`/`LeagueMember`, `/leaderboard` |
| Social + feed + logros | ✅ | `Social` (Livewire), `AchievementsService`, `ActivityFeed`, `/social` |
| Notificaciones | ✅ | `NotificationBell` (Livewire, badge + dropdown, poll 30s) |
| Stats de perfil | ✅ | `Progress` (Livewire), `/progress` (XP, racha, coronas, liga, logros) |
| Jobs / scheduler | ✅ | `RegenerateHearts` (cada 30 min) + `league:close-week` (domingo 23:00) |
| **API estilo Duolingo** | ✅ | `GET /api/v1/config`, `/courses` (flat, snake_case), `/users/{id}/progress` (`language_data`), `/leaderboard` (`active_leaderboard`) — alineada a REA |
| Tests | ✅ | `LessonFlowTest` + `LeagueAndHeartsTest` + `ApiTest` + suite Breeze (40 tests) |

### Pendiente (no bloqueado)
- Alinear la API propia al esquema real de Duolingo (**snake_case**) con API Resources: `learning_language`, `from_language`, `num_learners`, `phase`, `progress`.
- Añadir campos finos detectados: `hearts.seconds_per_heart_segment`, `hearts.unlimited_until`, `goals.monthly_challenge`.
- Regen de corazones ya programado; opcional: notificaciones push de racha.

### Reconciliado con REA (2026-10-16)
Ver `duolingo-re/REA-HALLAZGOS.md`. Capturado vía **CDP directo** + análisis de bundles JS (MCP `rea` seguía requiriendo reinicio):
- Endpoints: `/api/1/courses/list` (313 cursos), `/2023-05-23/config` (74 feature flags), `/users/{id}/streak-goal-*`, `POST excess.duolingo.com/batch` (telemetría), `zombie.duolingo.com/{id}/web.json` (presencia).
- **Superficie de API: 71 rutas en 32 hosts** con arquitectura de microservicios (leaderboards-prod, achievements-prod, goals-api, forum-api, spam-control-api…). Rutas: `/sessions/{lang}/{from}/{id}`, `/practice-hub/*`, `/users/{id}/{progress,quests,feed/v2,followers,matches}`, `/leaderboard`, `/shop`, `/shop-items`.
- Estado cliente `duo.state` = **jotai + redux (17 slices)**: confirma el modelo (hearts/items/goals/friends/user…).
- Artefactos en `duolingo-re/captura/` (`api-paths.txt`, `api-hosts.txt`, `duo.state.json`, `courses-list.json`, `config.json`).

### Pendiente (requiere MCP `rea` + login)
- `observe_web_session` / `analyze_javascript_application` (DOM/a11y/cobertura).
- Cuerpos de respuestas **autenticadas** (`/sessions`, `/users/{id}/progress`, `/leaderboard`, `/shop`) — rutas ya enumeradas.
