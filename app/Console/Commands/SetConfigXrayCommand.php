<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Inbound;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SetConfigXrayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xray:set-config';

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
        Artisan::call('servers:get-all');
        Artisan::call('users:get-all');

        $setting = Setting::query()->first();

        if ($setting->need_reset || $setting->changed_servers || $setting->changed_users) {
            $servers = Inbound::all();
            $users = Client::query()->where('banned', '=', 0)->get();

            $inboundConfigs = [];
            foreach ($servers as $server) {
                $inboundConfig = [
                    "listen" => "0.0.0.0",
                    "port" => 1234,
                    "protocol" => "vmess",
                    "settings" => [
                        "clients" => [
                        ]
                    ],
                    "streamSettings" => [
                        "network" => "ws",
                        "security" => "none"
                    ],
                    "tag" => $server->tag
                ];

                $inboundConfigs[] = $inboundConfig;
            }

            $inConfs = [];
            foreach ($inboundConfigs as $config) {
                foreach ($users as &$user) {
                    $user->email = $user->email . "_" . $config['tag'];
                    $user->id = $user->uuid;
                    $config['settings']['clients'][] = $user;

                    $inConfs[] = $config;
                }
            }

            $configFilePath = storage_path('app/config.json');
            $configData = json_decode(file_get_contents($configFilePath), true);

            $configData['inbounds'] = (array)[];

            $configData['inbounds'][0] = (object)[
                "listen" => "127.0.0.1",
                "port" => 8082,
                "protocol" => "dokodemo-door",
                "settings" => [
                    "address" => "127.0.0.1"
                ],
                "tag" => "api"
            ];

            foreach ($inConfs as $config) {
                $configData['inbounds'][] = (object)$config;
            }

            Log::info(json_encode($configData));

            $configJson = json_encode($configData, JSON_PRETTY_PRINT);
            file_put_contents($configFilePath, $configJson);

            Setting::query()->first()->update([
                'need_reset' => false,
                'changed_users' => false,
                'changed_servers' => false
            ]);

//            $commandKill = 'sudo kill -SIGHUP $(pgrep xray)';
//            $commandRun = 'sudo /usr/local/bin/xray run -c /var/www/x-b/app/config.json > /dev/null 2>&1 &';
//            shell_exec($commandKill);
//            sleep(3);
//            shell_exec($commandRun);
//
//            $pid = shell_exec('pgrep -n "sudo /usr/local/bin/xray run -c /var/www/x-b/app/config.json"');
//
//            Setting::query()->first()->update([
//                'process_id' => $pid
//            ]);
        }
    }
}
