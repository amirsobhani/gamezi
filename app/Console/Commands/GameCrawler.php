<?php

namespace App\Console\Commands;


use App\Services\UploadService;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Infrastructure\Interfaces\UploadServiceInterface;
use Modules\Game\Entities\Developer;
use Modules\Game\Entities\EsrbRating;
use Modules\Game\Entities\Game;
use Modules\Game\Entities\Genre;
use Modules\Game\Entities\Media;
use Modules\Game\Entities\Platform;
use Modules\Game\Entities\Publisher;

class GameCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:game';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl last game';

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
        $page = (int)floor(Game::query()->count() / 20) > 0 ? (int)ceil(Game::query()->count() / 20) : 1;

        $repeat_count = 0;

        $platforms = Platform::all();

        $fileService = new UploadService();

        while (true) {
//            echo $page . PHP_EOL;
//            try {
            $res = Http::get('https://rawg.io/api/games/lists/main', [
//                $res = Http::get('https://rawg.io/api/games', [
                'discover' => true,
                'ordering' => '-added',
                'page_size' => 20,
                'page' => $page,
                'key' => 'c542e67aec3a4340908f9de9e86038af'
            ]);
//                sleep(0.25);

//            dd($res->json(), $page);

            foreach ($res['results'] as $item) {

                echo $item['id'] . ' - ' . $item['name'] . PHP_EOL;

                $skip_condition = false;

                $tags_stop_word = ['sexual content', 'sex', 'hentai', 'sexy', 'adult', 'porn'];

                foreach ($item['tags'] as $tag) {
                    foreach ($tags_stop_word as $stop_word) {
                        if (str_contains(strtolower($tag['name']), $stop_word)) {
                            echo 'Nudity Detect !!!!!!!!!!!!!!!!!!!' . PHP_EOL;
                            if (is_null($item['metacritic']))
                                $skip_condition = true;
                        }
                    }
                }

                if (!$skip_condition) {
                    $exists = Game::query()->where('name', $item['name'])->exists();
                    $game_genre_ids = [];
                    $game_platform_ids = [];
                    $game_developer_ids = [];
                    $game_publisher_ids = [];
                    $tags = [];
                    $esrb_id = null;

                    if (!$exists) {
                        if (!empty($item['esrb_rating'])) {

                            $esrb = EsrbRating::query()->updateOrCreate([
                                'title' => $item['esrb_rating']['name'],
                                'id' => $item['esrb_rating']['id'],
                            ]);

                            $esrb_id = $esrb->id;
                        }

                        if (!empty($item['genres'])) {
                            foreach ($item['genres'] as $genres_item) {
                                $genre = Genre::query()->updateOrCreate([
                                    'title' => $genres_item['name']
                                ]);

                                $game_genre_ids[] = $genre->id;
                            }
                        }

                        if (!empty($item['platforms'])) {
                            foreach ($item['platforms'] as $platform_item) {
                                $platform = $platforms->where('alias', $platform_item['platform']['name'])->first();
                                if (!empty($platform)) {
                                    $game_platform_ids[] = $platform->id;
                                }
                            }
                        }

                        if (!empty($item['tags'])) {
                            foreach ($item['tags'] as $tag_item) {
                                $tags[] = $tag_item['name'];
                            }
                        }

                        $rawg_game = Http::get('https://rawg.io/api/games/' . $item['id'], [
                            'key' => 'c542e67aec3a4340908f9de9e86038af'
                        ]);

//                            $rawg_game_media = Http::get('https://rawg.io/api/games/' . $item['id'] . '/screenshots', [
//                                'key' => 'c542e67aec3a4340908f9de9e86038af'
//                            ]);

//                            sleep(0.25);

                        if (!empty($rawg_game['developers'])) {
                            foreach ($rawg_game['developers'] as $developer_item) {

                                $developer = Developer::query()->updateOrCreate([
                                    'alias' => $developer_item['name']
                                ]);

                                $game_developer_ids[] = $developer->id;
                            }
                        }

                        if (!empty($rawg_game['publishers'])) {
                            foreach ($rawg_game['publishers'] as $publisher_item) {

                                $publisher = Publisher::query()->updateOrCreate([
                                    'alias' => $publisher_item['name']
                                ]);

                                $game_publisher_ids[] = $publisher->id;
                            }
                        }

//                        echo 'game_genre_ids: ' . implode(',', $game_genre_ids) . PHP_EOL;
//                        echo 'game_platform_ids: ' . implode(',', $game_platform_ids) . PHP_EOL;
//                        echo 'game_developer_ids: ' . implode(',', $game_developer_ids) . PHP_EOL;
//                        echo 'game_publisher_ids: ' . implode(',', $game_publisher_ids) . PHP_EOL;
//                        echo 'esrb :' . $esrb_id . PHP_EOL;

                        $game = DB::transaction(function () use (
                            $fileService,
                            $repeat_count,
                            $page,
//                                $rawg_game_media,
                            $esrb_id,
                            $rawg_game,
                            $item,
                            $game_genre_ids,
                            $game_platform_ids,
                            $game_publisher_ids,
                            $game_developer_ids
                        ) {
                            $game = Game::query()->create([
                                'rawg_id' => $item['id'],
                                'esrb_rating_id' => $esrb_id,
                                'alias' => $item['name'],
                                'name' => $item['name'],
                                'description_en' => $rawg_game['description_raw'] ?? null,
                                'metacritic' => $rawg_game['metacritic'] ?? null,
                                'metacritic_url' => $rawg_game['metacritic_url'] ?? null,
                                'rating' => $rawg_game['rating'] ?? 0,
                                'rating_top' => $rawg_game['rating_top'] ?? null,
                                'playtime' => $rawg_game['playtime'] ?? 0,
                                'website' => $rawg_game['website'] ?? null,
                                'release_time' => $rawg_game['released'] ?? null,
                                'is_global' => true,
                                'tags' => empty($tags) ? null : implode(',', $tags)
                            ]);

                            $game_media_ids = [];


                            if (!empty($item['short_screenshots'])) {
                                foreach ($item['short_screenshots'] as $short_screenshot) {

                                    $url = $short_screenshot['image'];

                                    $uploadedFile = $fileService->uploadFileByUrl($url, 'short_screenshots', $game->id, 'games', 'games');

                                    $media = Media::query()->create($uploadedFile);

                                    $game_media_ids[] = $media->id;

                                }
                            }


                            if (!empty($item['background_image'])) {

                                $url = $item['background_image'];

                                $uploadedFile = $fileService->uploadFileByUrl($url, 'background', $game->id, 'games', 'games');

                                $game->update(['background_image' => $uploadedFile['path']]);
                            }

//                            echo 'game_media_ids: ' . implode(',', $game_media_ids) . PHP_EOL;
                            echo 'page : ' . $page . PHP_EOL;
                            echo 'requesting next game : .................' . PHP_EOL;

                            $game->developer()->sync($game_developer_ids);
                            $game->publisher()->sync($game_publisher_ids);
                            $game->platform()->sync($game_platform_ids);
                            $game->genre()->sync($game_genre_ids);
                            $game->media()->sync($game_media_ids);

                            return $game;
                        });

                        $game = $game->with('developer', 'publisher', 'genre', 'platform', 'esrbRating', 'media')
                            ->find($game->id)->toArray();

                        $developer_aliases = collect($game['developer'])->pluck('alias')->toArray();
                        $publisher_aliases = collect($game['publisher'])->pluck('alias')->toArray();

                        $game['full_text'] = $game['name'] . ' '
                            . $game['name'] . ' '
                            . $game['tags'] . ' '
                            . implode(',', $developer_aliases) . ' '
                            . implode(',', $publisher_aliases);

//                            $client = ClientBuilder::create()
//                                ->setHosts([env('ELASTIC_HOST')])
//                                ->build();

//                            $params = [
//                                'index' => Game::GameIndexName,
//                                'id' => $game['id'],
//                                'body' => $game
//                            ];

//                            $response = $client->index($params);

                    } else {
                        $repeat_count++;
                    }
                } else {
                    echo 'Skipping the game ................................' . PHP_EOL;
                }

                echo 'repeat_count : ' . $repeat_count . PHP_EOL;

//                if ($repeat_count > 50) {
//                    echo 'repeat_count : ' . $repeat_count . PHP_EOL;
//                    exit();
//                }
            }
//            }
//            catch (\Exception $exception) {
//                echo '***********************************************' . PHP_EOL;
//                echo $exception->getMessage() . '=>' . $exception->getLine() . PHP_EOL;
//                echo '***********************************************' . PHP_EOL;
//                if ($exception->getMessage() == 'Undefined array key "results"' or
//                    $exception->getMessage() == 'Invalid page."') {
//                    exit();
//                } else {
//                    continue;
//                }
//            }
            $page++;
        }
    }
}
