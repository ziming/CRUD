<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Query
 */
class CrudPanelQueryDatabaseTest extends BaseDBCrudPanel
{
    public function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setModel(User::class);
    }

    public function testItCanGetTheQueryCount()
    {
        $this->assertEquals(User::query()->count(), $this->crudPanel->query->count());
    }

    public function testItDoesNotCountTheQueryAgainIfThereAreNoFilters()
    {
        $this->assertNull($this->crudPanel->getFilteredQueryCount());
    }

    public function testItCountTheQueryAgainIfThereAreFilters()
    {
        $this->crudPanel->addClause('where', 'id', 1);

        $this->assertEquals(User::query()->where('id', 1)->count(), $this->crudPanel->getFilteredQueryCount());
    }

    public function testRawExpressionsDontGetRemovedFromCount()
    {
        $this->crudPanel->addClause(fn ($query) => $query->where(DB::raw('id = 1')));

        $this->assertEquals(User::query()->where(DB::raw('id = 1'))->count(), $this->crudPanel->getFilteredQueryCount());
    }
}
