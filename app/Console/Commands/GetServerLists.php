<?php

namespace App\Console\Commands;

use App\Models\Inbound;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetServerLists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servers:get-all';

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
        $configs = Http::retry(10, 1000)
            ->post(config('v2board.base_url') . "/server/UniProxy/allServers", [
                "token" => "nvFDdx8MvBXK4SduouKQEZ4xZD",
                "node_type" => "v2ray",
                "node_id" => "74"
            ])
            ->json()['data'];

        $filteredConfigs = array_filter($configs, function ($el) {
            return $el->config_flag == config('v2board.server_flag');
        });

        $filteredNodeIds = [];
        $needToBeReset = false;
        foreach ($filteredConfigs as $config) {
            $config = (object)$config;
            $filteredNodeIds[] = $config->node_id;
            $updateOrCreatedConfig = Inbound::query()->updateOrCreate([
                'node_id' => $config->node_id
            ], [
                'node_id' => $config->node_id,
                'protocol' => $config->protocol,
                'type' => $config->type,
                'port' => $config->port,
                'tag' => $config->tag,
                'listen' => $config->listen,
                'stream_settings' => json_encode($config->stream_settings),
                'network_settings' => json_encode($config->network_settings),
                'tls_settings' => json_encode($config->tls_settings),
                'rule_Settings' => json_encode($config->rule_Settings),
                'dns_settings' => json_encode($config->dns_settings),
                'rules' => json_encode($config->rules),
            ]);

            if (!$updateOrCreatedConfig->wasRecentlyCreated && $updateOrCreatedConfig->wasChanged()) {
                $needToBeReset = true;
            }
            if ($updateOrCreatedConfig->wasRecentlyCreated) {
                $needToBeReset = true;
            }
            if (!$updateOrCreatedConfig->wasRecentlyCreated && !$updateOrCreatedConfig->wasChanged()) {

            }
        }

        Inbound::query()
            ->whereNotIn('node_id', $filteredNodeIds)
            ->delete();

        if ($needToBeReset) {
            Setting::query()->first()->update([
                'need_reset' => true,
                'changed_servers' => true
            ]);
        }
    }
}
