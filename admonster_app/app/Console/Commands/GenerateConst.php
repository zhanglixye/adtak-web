<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateConst extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'const:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate const';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $constUrl = "config/const.php";

        if (file_exists($constUrl)) {
            $const = require($constUrl);
            $json = json_encode($const, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            file_put_contents("resources/assets/js/const.json", $json);
        } else {
            echo "データがありません";
        }
    }
}
