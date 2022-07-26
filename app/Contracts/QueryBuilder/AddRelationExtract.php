<?php

namespace App\Contracts\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Includes\IncludeInterface;

class AddRelationExtract implements IncludeInterface
{
    /**
     * Build constructor.
     *
     * @param array $with
     */
    public function __construct(public ?array $with = [])
    {
        //
    }

    /**
     * Invoke the query builder.
     *
     * @param Builder $query
     * @param string $include
     */
    public function __invoke(Builder $query, string $include)
    {
        $query->with([
            $include => fn($query) => $query->with($this->with),
        ]);
    }
}
