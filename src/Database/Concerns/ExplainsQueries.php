<?php

namespace Kasi\Database\Concerns;

use Kasi\Support\Collection;

trait ExplainsQueries
{
    /**
     * Explains the query.
     *
     * @return \Kasi\Support\Collection
     */
    public function explain()
    {
        $sql = $this->toSql();

        $bindings = $this->getBindings();

        $explanation = $this->getConnection()->select('EXPLAIN '.$sql, $bindings);

        return new Collection($explanation);
    }
}
