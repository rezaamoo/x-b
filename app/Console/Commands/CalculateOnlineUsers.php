<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CalculateOnlineUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:calculate-online';

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
        $command = "sudo /usr/local/bin/xray api statsquery --server=127.0.0.1:8080 -pattern '' -reset";
        $output = shell_exec($command);

        $users = [];

        $onlineServers = [];

        foreach (json_decode($output)->stat as $item) {
            $item = (object)$item;
            $itemToArray = explode('>>>', $item->name);
            if ($itemToArray[0] == 'user') {
                $server = explode("_", $itemToArray[1])[1];
                if ($itemToArray[3] == 'downlink') {
                    $item->value ? $online = 1 : $online = 0;
                    $subId = str_replace('@xrayback.xray', '', explode("_", $itemToArray[1])[0]);
                    $users[] = [
                        'sub_id' => $subId,
                        'server' => $server,
                        'online' => $online
                    ];

                    $onlineServers[$server]['count']++;
                    $onlineServers[$server]['users'][] = $subId;
                }
            }
        }


        Http::retry(10, 1000)
            ->post(config('v2board.base_url') . "/server/UniProxy/online_users_on_servers", [
                'servers' => $onlineServers,
                "token" => "nvFDdx8MvBXK4SduouKQEZ4xZD",
                "node_type" => "v2ray",
                "node_id" => "74"
            ]);
    }
}
