<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = config('article.no_of_news_per_page');

        $articles = Article::paginate($perPage, ['*'], 'page', $page);
        return response()->json(['status' => 'success', 'data' => $articles]);
    }

    public function search(Request $request)
    {
        $author = $request->input('author');
        if (!$author) {
            return response()->json(['status' => 'error', 'message' => 'Author parameter is required.']);
        }

        $result = Article::where('author', 'like', '%' . $author . '%')->get();
        return response()->json(['status' => 'success', 'data' => $result]);
    }

    public function filter(Request $request)
    {
        $date = $request->input('date');
        $source = $request->input('source');
        $category = $request->input('category');

        if (!$date || !$source || !$category) {
            return response()->json(['status' => 'error', 'message' => 'Date or category or source parameter is required.']);
        }

        $result = Article::where('publish_date', $date)
            ->orWhere('source', $source)
            ->orWhere('category', $category)
            ->get();

        return response()->json(['status' => 'success', 'data' => $result]);
    }

    public function userPreferences(Request $request)
    {
        $selectedSources = $request->input('sources', []);
        $selectedAuthors = $request->input('authors', []);
        $selectedCategories = $request->input('categories', []);

        if (empty($selectedAuthors) && empty($selectedSources) && empty($selectedCategories)) {
            return response()->json(['status' => 'error', 'message' => 'At least one author or source or category parameter is required.']);
        }

        $result = Article::when($selectedAuthors, function ($query) use ($selectedAuthors) {
            $query->whereIn('author', $selectedAuthors);
        })
            ->when($selectedSources, function ($query) use ($selectedSources) {
                $query->whereIn('source', $selectedSources);
            })
            ->when($selectedCategories, function ($query) use ($selectedCategories) {
                $query->whereIn('category', $selectedCategories);
            })
            ->get();

        return response()->json(['status' => 'success', 'data' => $result]);
    }
}
