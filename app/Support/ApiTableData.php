<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ApiTableData
{
    /**
     * Apply search, an optional filter callback, sorting, and pagination to an
     * in-memory collection of records sourced from the remote API, and return
     * a standard Laravel paginator Filament's table can render natively.
     *
     * @param  array<int, string>  $searchableFields
     */
    public static function paginate(
        Collection $items,
        ?string $search,
        array $searchableFields,
        ?string $sortColumn,
        ?string $sortDirection,
        int $perPage,
        int $page,
        ?callable $filterCallback = null,
    ): LengthAwarePaginator {
        if ($filterCallback) {
            $items = $filterCallback($items);
        }

        if (filled($search)) {
            $needle = Str::lower($search);

            $items = $items->filter(function ($item) use ($needle, $searchableFields) {
                foreach ($searchableFields as $field) {
                    if (Str::contains(Str::lower(self::stringify(data_get($item, $field))), $needle)) {
                        return true;
                    }
                }

                return false;
            });
        }

        if (filled($sortColumn)) {
            $items = $items->sortBy(
                fn ($item) => data_get($item, $sortColumn),
                SORT_REGULAR,
                $sortDirection === 'desc',
            )->values();
        }

        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($slice, $total, $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }

    private static function stringify(mixed $value): string
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => is_array($item) ? ($item['name'] ?? json_encode($item)) : $item)
                ->implode(' ');
        }

        return (string) ($value ?? '');
    }
}
