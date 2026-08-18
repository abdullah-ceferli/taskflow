<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface GlobalSearchInterface
{
    /** @return Collection<int, array{type: string, id: int, title: string, excerpt: string, url: string}> */
    public function search(User $actor, string $query, int $limitPerType = 10): Collection;
}
