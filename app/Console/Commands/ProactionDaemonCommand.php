<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Proaction\Domain\Tasks\Model\TaskStatus;
use Proaction\System\Resource\Helpers\Arr;

/**
 * Allows for using the Proaction Daemon from the CLI with the Laravel
 * setup
 */
class ProactionDaemonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proaction:daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        // Need to create the ShellDaemon script in here
        // loop through meta client data, set the 'cli_prefix' value
        // and then run the shellDaemon
        $GLOBALS['cli_prefix'] = 'jasoncases';
        echo getClientPrefix();
    }
}
