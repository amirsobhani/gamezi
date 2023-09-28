<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Game\Entities\Developer;

class DeveloperCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:developer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl last developer';

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

        try {
            while (true) {
                $res = Http::get('https://rawg.io/api/developers', [
                    'discover' => true,
                    'ordering' => '-added',
                    'page_size' => 20,
                    'page' => $page,
                    'key' => 'c542e67aec3a4340908f9de9e86038af'
                ]);
                sleep(1);
                foreach ($res['results'] as $item) {
                    $exists = Developer::query()->where('alias', $item['name'])->exists();

                    if (!$exists) {
                        Developer::query()->create(
                            [
                                'alias' => $item['name']
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
        } catch (\Exception $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }

    }
}
