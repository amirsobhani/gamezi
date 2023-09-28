<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Game\Entities\Platform;
use Modules\Game\Entities\PlatformCategory;

class PlatformCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:platform';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl last platform';

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
        $platform_categories = PlatformCategory::all();

        $res = Http::get('https://rawg.io/api/platforms', [
            'discover' => true,
            'ordering' => '-added',
            'key' => 'c542e67aec3a4340908f9de9e86038af'
        ]);

        foreach ($res['results'] as $item) {
//            dd($platform_categories->where('name', '%like%', $item['name'])->first());
            Platform::query()->updateOrCreate(
                [
                    'alias' => $item['name']
                ],
                [
                    'platform_category_id' => $platform_categories->where('name', 'REGEXP', $item['name'])->first()?->id ?? $platform_categories->first()->id,
                    'rawg_id' => $item['id'],
                    'slug' => $item['slug'],
                    'release_time' => $item['year_start'],
                    'end_time' => $item['year_end']
                ]
            );

            echo $item['name'] . PHP_EOL;
        }

        sleep(1);
    }
}
