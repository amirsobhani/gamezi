<?php

namespace App\Console\Commands;

use Game_Platform\Models\Developer;
use Game_Platform\Models\Platform;
use Game_Platform\Models\PlatformCategory;
use Game_Platform\Models\Publisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PublisherCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:publisher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl last publisher';

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
        $repeat_count = 0;
        $page = 1;

        while (true) {
            $res = Http::get('https://rawg.io/api/publishers', [
                'discover' => true,
                'ordering' => '-added',
                'page_size' => 20,
                'page' => $page,
                'key' => 'c542e67aec3a4340908f9de9e86038af'
            ]);
            sleep(1);
            foreach ($res['results'] as $item) {
                $exists = Publisher::query()->where('alias', $item['name'])->exists();

                if (!$exists) {
                    Publisher::query()->create(
                        [
                            'alias' => $item['name'],
                            'rawg_id' => $item['id']
                        ]
                    );
                } else {
                    $repeat_count++;
                }

                echo $item['name'] . PHP_EOL;
            }
            if ($repeat_count > 20) {
                exit();
            }
            $page++;
        }

    }
}
