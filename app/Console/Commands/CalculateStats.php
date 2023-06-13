<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class CalculateStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:calculate';

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
        foreach ($output as $item) {
            $itemToArray = explode('>>>', $item->name);
            if ($itemToArray[0] == 'user') {
                $server = explode("_", $itemToArray[1])[1];
                if ($itemToArray[3] == 'downlink') {
                    $users[] = [
                        'sub_id' => str_replace('@xrayback.xray', '', explode("_", $itemToArray[1])[0]),
                        'd' => $item->value,
                        'server' => $server
                    ];
                } elseif ($itemToArray[3] == 'uplink') {
                    $users[] = [
                        'sub_id' => str_replace('@xrayback.xray', '', explode("_", $itemToArray[1])[0]),
                        'u' => $item->value,
                        'server' => $server
                    ];
                }
            }
        }

        return $users;
    }
}
