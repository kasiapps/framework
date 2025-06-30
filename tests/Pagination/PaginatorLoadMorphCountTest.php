<?php

namespace Kasi\Tests\Pagination;

use Kasi\Database\Eloquent\Collection;
use Kasi\Pagination\AbstractPaginator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PaginatorLoadMorphCountTest extends TestCase
{
    public function testCollectionLoadMorphCountCanChainOnThePaginator()
    {
        $relations = [
            'App\\User' => 'photos',
            'App\\Company' => ['employees', 'calendars'],
        ];

        $items = m::mock(Collection::class);
        $items->shouldReceive('loadMorphCount')->once()->with('parentable', $relations);

        $p = (new class extends AbstractPaginator {})->setCollection($items);

        $this->assertSame($p, $p->loadMorphCount('parentable', $relations));
    }
}
