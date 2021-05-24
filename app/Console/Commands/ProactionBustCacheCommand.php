<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Proaction\Shell\Cache\CacheDaemon;
use Proaction\System\Resource\Cache\Cache;
use Proaction\System\Resource\Cache\ProactionRedis;

class ProactionBustCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proaction:bust';

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
        // process cache values, same issue w/ the prefix
        $cache = new Cache('laravel', ProactionRedis::getInstance());
        $cache->bustCache();
        $cache->process();
    }
}
