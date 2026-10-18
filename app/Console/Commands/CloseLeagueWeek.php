<?php

namespace App\Console\Commands;

use App\Services\LeagueService;
use Illuminate\Console\Command;

class CloseLeagueWeek extends Command
{
    protected $signature = 'league:close-week {--promote=3} {--demote=3}';

    protected $description = 'Snapshot league standings and promote/demote members for the next week';

    public function handle(LeagueService $leagues): int
    {
        $result = $leagues->closeWeek(
            (int) $this->option('promote'),
            (int) $this->option('demote'),
        );

        $this->info(sprintf(
            'Liga cerrada. Ascendidos: %d · Descendidos: %d',
            count($result['promoted']),
            count($result['demoted']),
        ));

        return self::SUCCESS;
    }
}
