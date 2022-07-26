<?php

namespace App\Contracts\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Includes\IncludeInterface;

class BasicInfoExtract implements IncludeInterface
{
    /**
     * Build constructor.
     *
     * @param array|null $values
     */
    public function __construct(public ?array $values = null)
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
        $values = $this->values ?? [
            'id',
        ];

        $query->with([
            $include => fn($query) => $query->select($values),
        ]);
    }
}
