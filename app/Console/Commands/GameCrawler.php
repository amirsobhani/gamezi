<?php

namespace App\Console\Commands;


use Elasticsearch\ClientBuilder;
use Game_Platform\Models\Developer;
use Game_Platform\Models\EsrbRating;
use Game_Platform\Models\Game;
use Game_Platform\Models\Genre;
use Game_Platform\Models\Media;
use Game_Platform\Models\Platform;
use Game_Platform\Models\Publisher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use function Modules\Game\Console\public_path;
use function Modules\Game\Console\str_contains;

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
        $page = floor(Game::query()->count() / 20);
        $repeat_count = 0;

        $esrbs = EsrbRating::all();
        $genres = Genre::all();
        $platforms = Platform::all();

        while (true) {
            try {
                $res = Http::get('https://rawg.io/api/games/lists/main', [
//                $res = Http::get('https://rawg.io/api/games', [
                    'discover' => true,
                    'ordering' => '-added',
                    'page_size' => 20,
                    'page' => $page,
                    'key' => 'c542e67aec3a4340908f9de9e86038af'
                ]);

//                sleep(0.25);

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
                        $exists = Game::query()->where('alias', $item['name'])->exists();
                        $game_genre_ids = [];
                        $game_platform_ids = [];
                        $game_developer_ids = [];
                        $game_publisher_ids = [];
                        $tags = [];
                        $esrb_id = null;

                        if (!$exists) {
                            if (!empty($item['esrb_rating'])) {
                                $esrb = $esrbs->where('title', $item['esrb_rating']['name'])->first();
                                if (!empty($esrb)) {
                                    $esrb_id = $esrb->id;
                                }
                            }

                            if (!empty($item['genres'])) {
                                foreach ($item['genres'] as $genres_item) {
                                    $genre = $genres->where('title', $genres_item['name'])->first();
                                    if (!empty($genre)) {
                                        $game_genre_ids[] = $genre->id;
                                    }
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
                                    $developer = Developer::query()->where('alias', $developer_item['name'])->first();
                                    if (!empty($developer)) {
                                        $game_developer_ids[] = $developer->id;
                                    }
                                }
                            }

                            if (!empty($rawg_game['publishers'])) {
                                foreach ($rawg_game['publishers'] as $publisher_item) {
                                    $publisher = Publisher::query()->where('alias', $publisher_item['name'])->first();
                                    if (!empty($publisher)) {
                                        $game_publisher_ids[] = $publisher->id;
                                    }
                                }
                            }

//                        echo 'game_genre_ids: ' . implode(',', $game_genre_ids) . PHP_EOL;
//                        echo 'game_platform_ids: ' . implode(',', $game_platform_ids) . PHP_EOL;
//                        echo 'game_developer_ids: ' . implode(',', $game_developer_ids) . PHP_EOL;
//                        echo 'game_publisher_ids: ' . implode(',', $game_publisher_ids) . PHP_EOL;
//                        echo 'esrb :' . $esrb_id . PHP_EOL;

                            $game = DB::transaction(function () use (
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
                                    'description_en' => $rawg_game['description_raw'],
                                    'metacritic' => $rawg_game['metacritic'],
                                    'metacritic_url' => $rawg_game['metacritic_url'],
                                    'rating' => $rawg_game['rating'],
                                    'rating_top' => $rawg_game['rating_top'],
                                    'playtime' => $rawg_game['playtime'],
                                    'website' => $rawg_game['website'],
                                    'release_time' => $rawg_game['released'],
                                    'is_global' => true,
                                    'tags' => empty($tags) ? null : implode(',', $tags)
                                ]);

                                $game_media_ids = [];

                                $img_path = public_path('media/games/' . $game->id);

                                if (!file_exists($img_path)) {
                                    mkdir($img_path, 0777, true);
                                }

//                                foreach ($rawg_game_media['results'] as $item) {
//                                    if (!empty($item['image'])) {
//                                        $img_arr = explode('/', $item['image']);
//                                        $img_title = end($img_arr);
//                                        $contents = file_get_contents($item['image']);
//                                        file_put_contents($img_path . '/' . $img_title, $contents);
//
//                                        $media = Media::query()->create([
//                                            'url' => env('APP_URL') . '/media/games/' . $game->id . '/' . $img_title,
//                                            'type' => Media::ImageType
//                                        ]);
//
//                                        $game_media_ids[] = $media->id;
//                                    }
//                                }

                                if (!empty($rawg_game['background_image'])) {
                                    $img_arr = explode('/', $rawg_game['background_image']);
                                    $img_title = end($img_arr);
                                    $contents = file_get_contents($rawg_game['background_image']);
//                                    echo $img_path . '/' . $img_title. PHP_EOL;
                                    file_put_contents($img_path . '/' . $img_title, $contents);
                                    // Create an image resource
                                    $im = imagecreatefromjpeg($img_path . '/' . $img_title);
                                    // Decrease the quality of the image to 50
                                    imagejpeg($im, $img_path . '/' . $img_title, 50);

                                    $game->update(['background_image' => env('APP_URL') . '/media/games/' . $game->id . '/' . $img_title]);
                                }

//                            echo 'game_media_ids: ' . implode(',', $game_media_ids) . PHP_EOL;
                                echo 'page : ' . $page . PHP_EOL;
                                echo 'requesting next game : .................' . PHP_EOL;

                                $game->developer()->sync($game_developer_ids);
                                $game->publisher()->sync($game_publisher_ids);
                                $game->mainPlatform()->sync($game_platform_ids);
                                $game->genre()->sync($game_genre_ids);
                                $game->media()->sync($game_media_ids);

                                return $game;
                            });

                            $game = $game->with('developer', 'publisher', 'genre', 'platform', 'esrbRating', 'media')
                                ->find($game->id)->toArray();

                            $developer_aliases = collect($game['developer'])->pluck('alias')->toArray();
                            $publisher_aliases = collect($game['publisher'])->pluck('alias')->toArray();

                            $game['full_text'] = $game['alias'] . ' '
                                . $game['name'] . ' '
                                . $game['tags'] . ' '
                                . implode(',', $developer_aliases) . ' '
                                . implode(',', $publisher_aliases);

                            $client = ClientBuilder::create()
                                ->setHosts([env('ELASTIC_HOST')])
                                ->build();

                            $params = [
                                'index' => Game::GameIndexName,
                                'id' => $game['id'],
                                'body' => $game
                            ];

                            $response = $client->index($params);

                        } else {
                            $repeat_count++;
                        }
                    } else {
                        echo 'Skipping the game ................................' . PHP_EOL;
                    }

                    if ($repeat_count > 30) {
                        echo 'repeat_count : '.$repeat_count. PHP_EOL;
                        exit();
                    }
                }
            } catch (\Exception $exception) {
                echo '***********************************************' . PHP_EOL;
                echo $exception->getMessage() . '-' . $exception->getLine() . PHP_EOL;
                echo '***********************************************' . PHP_EOL;
                if ($exception->getMessage() == 'Undefined array key "results"' or
                    $exception->getMessage() == 'Invalid page."') {
                    exit();
                } else {
                    continue;
                }
            }
            $page++;
        }
    }
}
