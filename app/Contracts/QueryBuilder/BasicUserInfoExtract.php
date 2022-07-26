<?php

namespace App\Contracts\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Includes\IncludeInterface;

class BasicUserInfoExtract implements IncludeInterface
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
    public function __invoke(Builder $query, string $user)
    {
        $values = $this->values ?? [
            'id',
            'first_name',
            'last_name',
            'email',
            'phone_number',
        ];

        $query->with([
            $user => fn($query) => $query->select($values),
        ]);
    }
}
