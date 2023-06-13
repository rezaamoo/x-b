<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:xray';

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
        $command = "sudo /usr/local/bin/xray api statsquery --server=127.0.0.1:8082 -pattern '' -reset";
        $command1 = 'sudo kill -SIGHUP $(pgrep xray)';
        $command2 = 'sudo /usr/local/bin/xray run -c /var/www/Xray-install/config.json > /dev/null 2>&1 &';


        Log::info(base_path());
    }
}
