<?php

namespace App\Orchid\Concerns;

use App\Support\ApiTableData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Sorts, searches, and paginates an in-memory collection of records sourced
 * from the Verdantt API, following the same `?sort=-column&search=...&page=`
 * query conventions Orchid's own Eloquent-backed tables use, so TD::sort()
 * links and pagination controls work without an Eloquent query underneath.
 */
trait PaginatesApiCollection
{
    /**
     * @param  array<int, string>  $searchableFields
     */
    protected function paginateApi(
        Collection $items,
        Request $request,
        array $searchableFields,
        string $defaultSort = 'id',
        int $perPage = 10,
        ?callable $filterCallback = null,
    ): LengthAwarePaginator {
        $sortRaw = collect($request->collect('sort'))->first();
        $sortColumn = $sortRaw ? ltrim((string) $sortRaw, '-') : $defaultSort;
        $sortDirection = $sortRaw && str_starts_with((string) $sortRaw, '-') ? 'desc' : 'asc';

        return ApiTableData::paginate(
            $items,
            $request->query('search'),
            $searchableFields,
            $sortColumn,
            $sortDirection,
            $perPage,
            max((int) $request->query('page', 1), 1),
            $filterCallback,
        );
    }
}
