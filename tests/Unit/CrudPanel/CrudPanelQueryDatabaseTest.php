<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\CrudPanel\NoSqlDriverCrudPanel;
use Backpack\CRUD\Tests\config\Models\User;

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
        $this->assertEquals(User::query()->count(), $this->crudPanel->count());
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
        $this->crudPanel->addClause(fn ($query) => $query->selectRaw('id')->whereRaw('id = 1'));

        $this->assertEquals(1, $this->crudPanel->getFilteredQueryCount());
    }

    public function testItDoesNotPerformCountWhenCrudPanelDoesNoUseASqlDriver()
    {
        $this->crudPanel = new NoSqlDriverCrudPanel();
        $this->crudPanel->setModel(User::class);

        $this->assertNull($this->crudPanel->getFilteredQueryCount());
        $this->assertEquals(2, $this->crudPanel->getQueryCount());
    }
}
