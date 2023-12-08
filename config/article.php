<?php


return [

    /*
    |--------------------------------------------------------------------------
    | API keys and URLs
    |--------------------------------------------------------------------------
    */

    'nyt_api_key' => env('NYT_API_KEY', 'RiBTWUao1thGlCL5S73qLNExKIXPw3se'),
    'nyt_api_url' => env('NYT_API_URL', 'https://api.nytimes.com/svc/search/v2/articlesearch.json'),

    'news_api_key' => env('NEWSAPI_API_KEY', '6361b11bf9724d05a83d231df29604e5'),
    'news_api_url' => env('NEWSAPI_API_URL', 'https://newsapi.org/v2/everything'),

    'guardian_api_key' => env('GUARDIAN_API_KEY', 'b60ae673-ac5d-455b-8856-8847765ab47a'),
    'guardian_api_url' => env('GUARDIAN_API_URL', 'https://content.guardianapis.com/search'),

    'no_of_news_per_page' => env('NO_OF_NEWS_PER_PAGE', 10),

];
