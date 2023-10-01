<?php

namespace App\Console\Commands;



use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\Game\Entities\Genre;

class GenreCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:genre';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl last genre';

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

        $res = Http::get('https://rawg.io/api/genres', [
            'discover' => true,
            'ordering' => '-added',
            'key' => 'c542e67aec3a4340908f9de9e86038af'
        ]);
        sleep(1);
        foreach ($res['results'] as $item) {
            Genre::query()->updateOrCreate(
                [
                    'title' => $item['name']
                ]
            );

            echo $item['name'] . PHP_EOL;
        }

    }
}
