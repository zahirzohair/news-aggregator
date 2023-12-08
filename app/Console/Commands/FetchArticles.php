<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class FetchArticles extends Command
{
    protected $signature = 'fetch:articles';

    protected $description = 'Fetch articles from selected sources and store them locally';

    private $httpClient;

    // Injecting the HTTP client
    public function __construct(Http $httpClient)
    {
        parent::__construct();

        $this->httpClient = Http::withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch articles from NewsAPI
        $this->fetchFromNewsAPI();

        // // Fetch articles from NYT
        $this->fetchFromNewYorkTimes();

        // Fetch articles from Guardian
        $this->fetchFromGuardian();

        $this->info('Articles fetched successfully.');
    }

    private function fetchFromNewsAPI()
    {
        try {
            $newsApiKey = config('article.news_api_key');
            $newsUrl = config('article.news_api_url');

            $newsApiResponse = $this->httpClient->get($newsUrl, [
                'q' => 'politics',
                'sortBy' => 'popularity',
                'apiKey' => $newsApiKey,
            ]);

            if ($newsApiResponse->status() == 200) {
                $articles = $newsApiResponse->json()['articles'];

                foreach ($articles as $article) {
                    $fields = $this->extractCommonFields($article, 'newsapi');
                    $this->storeArticles($fields);
                }
            } else {
                Log::error('Invalid authentication credentials for NewsAPI');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching articles from NewsAPI: ' . $e->getMessage());
        }
    }

    private function fetchFromNewYorkTimes()
    {
        try {
            $nytApiKey = config('article.nyt_api_key');
            $nytUrl = config('article.nyt_api_url');

            $nytResponse = $this->httpClient->get($nytUrl, [
                'q' => 'sports',
                'api-key' => $nytApiKey,
            ]);

            if ($nytResponse->status() == 200) {
                $articles = $nytResponse->json()['response']['docs'];

                foreach ($articles as $article) {
                    $fields = $this->extractCommonFields($article, 'nytimes');
                    $this->storeArticles($fields);
                }
            } else {
                Log::error('Invalid authentication credentials for New York Times API');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching articles from New York Times: ' . $e->getMessage());
        }
    }

    private function fetchFromGuardian()
    {
        try {
            $guardianApiKey = config('article.guardian_api_key');
            $guardianUrl = config('article.guardian_api_url');

            $guardianResponse = $this->httpClient->get($guardianUrl, [
                'q' => 'politics',
                'api-key' => $guardianApiKey,
            ]);

            if ($guardianResponse->status() == 200) {
                $articles = $guardianResponse->json()['response']['results'];

                foreach ($articles as $article) {
                    $fields = $this->extractCommonFields($article, 'guardian');
                    $this->storeArticles($fields);
                }
            } else {
                Log::error('Invalid authentication credentials for Guardian API');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching articles from Guardian: ' . $e->getMessage());
        }
    }

    private function extractCommonFields($article, $source)
    {
        switch ($source) {
            case 'newsapi':
                return [
                    'author' => $article['author'],
                    'category' => '',
                    'source' => $article['source']['name'],
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'publish_date' => $this->parseDate($article['publishedAt']),
                ];
            case 'nytimes':
                $authorWithBy = $article['byline']['original'];
                $wordToReplace = "By";
                $author = str_replace($wordToReplace, "", $authorWithBy);
                return [
                    'author' => $author,
                    'category' => $article['section_name'],
                    'source' => $article['source'],
                    'title' => $article['headline']['main'],
                    'content' => $article['lead_paragraph'],
                    'publish_date' => $this->parseDate($article['pub_date']),
                ];
            case 'guardian':
                return [
                    'author' => 'Guardian',
                    'category' => $article['sectionName'],
                    'source' => $article['webUrl'],
                    'title' => $article['webTitle'],
                    'content' => $article['webUrl'],
                    'publish_date' => $this->parseDate($article['webPublicationDate']),
                ];
            default:
                return [];
        }
    }

    private function parseDate($dateString)
    {
        $carbonDate = Carbon::parse($dateString);
        return $carbonDate->format('Y-m-d H:i:s');
    }

    private function storeArticles($fields)
    {
        Article::insert($fields);
    }
}
