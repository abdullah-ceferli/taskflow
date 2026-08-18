<?php

namespace App\Http\Controllers;

use App\Contracts\GlobalSearchInterface;
use App\Http\Requests\GlobalSearchRequest;
use Illuminate\View\View;

final class GlobalSearchController extends Controller
{
    public function __invoke(GlobalSearchRequest $request, GlobalSearchInterface $search): View
    {
        $query = $request->string('q')->trim()->toString();

        return view('search.index', ['query' => $query, 'results' => $search->search($request->user(), $query)]);
    }
}
