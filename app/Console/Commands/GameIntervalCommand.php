<?php

namespace App\Console\Commands;

use App\Enums\Statuses;
use App\Enums\UserStatuses;
use App\Events\EndGameEvent;
use App\Events\GameIntervalEvent;
use App\Events\ThiefReleasedEvent;
use App\Models\Game;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use const Grpc\STATUS_ABORTED;

class GameIntervalCommand extends Command
{
    protected $signature = 'game:interval {--log}';
    protected $description = 'Updates all on-going games';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->log('Interval started');
        $now = Carbon::now();

        try {
            $games = Game::where('status', '=', Statuses::Ongoing)->get();
            foreach ($games as $game) {
                $this->log('  Interval game ' . $game->id);
                $game_ended = $this->hasGameTimeElapsed($game, $now);

                if (!$game_ended) {
                    $this->log('    Game is on-going');
                    if ($game->last_interval_at == null) {
                        $difference = 99999999;
                        $game->last_interval_at = $now;
                    } else
                        $difference = $now->diffInSeconds(Carbon::parse($game->last_interval_at ?? $game->started_at));
                    $this->log('    ' . $difference . ' seconds have elapsed since last interval');

                    $this->releasePlayersIfTimeHasElapsed($game, $now);

                    if ($difference >= $game->interval) {
                        $this->log('    Invoking interval event');
                        event(new GameIntervalEvent($game->id, $game->get_users_with_role(), $game->loot));
                        $game->last_interval_at = $now;
                    }
                } else {
                    $this->log('    Game has elapsed');
                    $game->status = Statuses::Finished;
                    $game->time_left = 0;

                    $users = $game->get_users();
                    foreach ($users as $user) {
                        $user->status = UserStatuses::Retired;
                        $user->save();
                    }

                    event(new EndGameEvent($game->id, 'De tijd is op. Het spel is beëindigd.'));
                }

                $game->save();
            }
        } catch (Exception $exception) {
            echo "An error occurred: \n\r" . $exception->getTraceAsString() . "\n\r";
        }
        $this->log('Interval ended');
        return 0;
    }

    private function releasePlayersIfTimeHasElapsed(Game $game, Carbon $now)
    {
        $caught_users = $game->get_users()->where('status', '=', UserStatuses::Caught);

        foreach ($caught_users as $user) {
            if (Carbon::parse($user->caught_at)->diffInMinutes($now) >= $game->jail_time) {
                $user->status = UserStatuses::Playing;
                $user->caught_at = null;
                $user->save();
                event(new ThiefReleasedEvent($user));
                $this->log("    Releasing player " . $user->id . " of game " . $game->id);
            }
        }
    }

    private function hasGameTimeElapsed(Game $game, Carbon $now)
    {
        $game->time_left -= $now->diffInSeconds(Carbon::parse($game->updated_at));
        if ($game->time_left <= 0)
            return true;
        return false;
    }

    private function log($message)
    {
        if ($this->option('log')) {
            Log::debug($message);
        }
    }
}
