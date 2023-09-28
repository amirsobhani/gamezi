<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Game\Entities\Platform;
use Modules\Game\Entities\PlatformCategory;

class PlatformCategoryCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:platform-cat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl platform category';

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
     */
    public function handle()
    {

        $res = Http::get('https://rawg.io/api/platforms/lists/parents', [
            'key' => 'c542e67aec3a4340908f9de9e86038af'
        ]);

        foreach ($res['results'] as $item) {
            PlatformCategory::query()->updateOrCreate(
                [
                    'name' => $item['name']
                ],
                [
                    'slug' => $item['slug'],
                ]
            );

            echo $item['name'] . PHP_EOL;
        }

    }
}
