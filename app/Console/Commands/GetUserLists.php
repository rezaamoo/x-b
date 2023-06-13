<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetUserLists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:get-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = Http::retry(10, 1000)
            ->post(config('v2board.base_url') . "/server/UniProxy/allUsers", [
                "token" => "nvFDdx8MvBXK4SduouKQEZ4xZD",
                "node_type" => "v2ray",
                "node_id" => "74"
            ])
            ->json()['users'];

        $needToBeReset = false;
        foreach ($users as $user) {
            $user = (object)$user;
            $updateOrCreatedUser = Client::query()->updateOrCreate([
                'sub_id' => $user->id
            ], [
                'sub_id' => $user->id,
                'uuid' => $user->uuid,
                'banned' => $user->banned,
                'email' => $user->id . "@xrayback.xray",
                'level' => 0,
                'alterId' => 64,
            ]);

            if (!$updateOrCreatedUser->wasRecentlyCreated && $updateOrCreatedUser->wasChanged()) {
                $needToBeReset = true;
            }
            if ($updateOrCreatedUser->wasRecentlyCreated) {
                $needToBeReset = true;
            }
            if (!$updateOrCreatedUser->wasRecentlyCreated && !$updateOrCreatedUser->wasChanged()) {

            }
        }

        if ($needToBeReset) {
            Setting::query()->first()->update([
                'need_reset' => true,
                'changed_users' => true
            ]);
        }
    }
}
