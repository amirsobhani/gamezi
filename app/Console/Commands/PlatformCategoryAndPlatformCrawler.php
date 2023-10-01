<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Game\Entities\Platform;
use Modules\Game\Entities\PlatformCategory;

class PlatformCategoryAndPlatformCrawler extends Command
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
            $category = PlatformCategory::query()->updateOrCreate(
                [
                    'name' => $item['name']
                ],
                [
                    'slug' => $item['slug'],
                ]
            );

            foreach ($item['platforms'] as $platform) {
                Platform::query()->updateOrCreate(
                    [
                        'alias' => $platform['name']
                    ],
                    [
                        'platform_category_id' => $category->id,
                        'rawg_id' => $platform['id'],
                        'slug' => $platform['slug'],
                        'release_time' => $platform['year_start'],
                        'end_time' => $platform['year_end']
                    ]
                );
            }

            echo $item['name'] . PHP_EOL;
        }

    }
}
