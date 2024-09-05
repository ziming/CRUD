<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;
use Backpack\CRUD\Tests\config\Models\User;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Query
 */
class CrudPanelQueryTest extends BaseCrudPanel
{
    public function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setModel(User::class);
    }

    public function testItHasABaseQuery()
    {
        $this->assertEquals(User::query()->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanAddAClauseToTheQuery()
    {
        $this->crudPanel->addClause('where', 'id', 1);

        $this->assertEquals(User::query()->where('id', 1)->toSql(), $this->crudPanel->query->toSql());
        $this->assertEquals(User::query()->toSql(), $this->crudPanel->totalQuery->toSql());
    }

    public function testItCanAddAClauseToTheQueryUsingAClosure()
    {
        $closure = function ($query) {
            $query->where('id', 1);
        };

        $this->crudPanel->addClause($closure);

        $this->assertEquals(User::query()->where('id', 1)->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanAddABaseClauseToTheQuery()
    {
        $this->crudPanel->addBaseClause('where', 'id', 1);

        $this->assertEquals(User::query()->where('id', 1)->toSql(), $this->crudPanel->query->toSql());
        $this->assertEquals(User::query()->where('id', 1)->toSql(), $this->crudPanel->totalQuery->toSql());
    }

    public function testItCanAddABaseClauseToTheQueryUsingAClosure()
    {
        $closure = function ($query) {
            $query->where('id', 1);
        };

        $this->crudPanel->addBaseClause($closure);

        $this->assertEquals(User::query()->where('id', 1)->toSql(), $this->crudPanel->totalQuery->toSql());
    }

    public function itCanAddDefaultOrderToTheyQuery()
    {
        $this->crudPanel->orderBy('id');

        $this->assertEquals(User::query()->orderBy('id', 'asc')->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanAddDefaultOrderToTheQueryWithDirection()
    {
        $this->crudPanel->orderBy('id', 'desc');

        $this->assertEquals(User::query()->orderBy('id', 'desc')->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItDoesNotSetOrderIfOrderIsInRequest()
    {
        $this->setupUserCreateRequest();

        $this->crudPanel->getRequest()->merge(['order' => 'id']);

        $this->crudPanel->orderBy('id', 'desc');

        $this->assertEquals(User::query()->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanAddAGroupByToTheyQuery()
    {
        $this->crudPanel->groupBy('name');

        $this->assertEquals(User::query()->groupBy('name')->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanAddALimitToTheQuery()
    {
        $this->crudPanel->limit(5);
        $this->assertEquals(User::query()->limit(5)->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanTakeSomeAmountFromTheQuery()
    {
        $this->crudPanel->take(5);
        $this->assertEquals(User::query()->take(5)->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanSkipSomeAmountFromTheQuery()
    {
        $this->crudPanel->skip(5);
        $this->assertEquals(User::query()->skip(5)->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanApplyCustomOrderLogicFromAColumn()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'text',
            'orderLogic' => function ($query, $column, $direction) {
                $query->orderBy('name', $direction);
            },
        ]);

        $this->crudPanel->customOrderBy($this->crudPanel->columns()['name'], 'asc');

        $this->assertEquals(User::query()->orderBy('name', 'asc')->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItSkipsCustomOrderLogicIfNotCustomOrderIsSet()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'text',
        ]);

        $this->crudPanel->customOrderBy($this->crudPanel->columns()['name'], 'asc');

        $this->assertEquals(User::query()->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItSkipsCustomOrderLogicIfOrderLogicIsNotCallable()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'text',
            'orderLogic' => 'not callable',
        ]);

        $this->crudPanel->customOrderBy($this->crudPanel->columns()['name'], 'asc');

        $this->assertEquals(User::query()->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanApplyAOrderByWithPrefix()
    {
        $this->crudPanel->orderByWithPrefix('name');

        $this->assertEquals(User::query()->orderBy('name', 'asc')->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItCanApplyAOrderByWithPrefixOnQueriesWithJoins()
    {
        $this->crudPanel->query = User::query()->join('articles', 'articles.user_id', '=', 'users.id');

        $this->crudPanel->orderByWithPrefix('name');

        $this->assertEquals(User::query()->join('articles', 'articles.user_id', '=', 'users.id')->orderBy('users.name', 'asc')->toSql(), $this->crudPanel->query->toSql());
    }

    public function testItDoesNotPerformQueryCountWhenItsDisabled()
    {
        $this->crudPanel->setOperationSetting('showEntryCount', false);

        $this->assertEquals(0, $this->crudPanel->getTotalQueryCount());
    }

    public function testItGetTheQueryCountFromAPreviousCount()
    {
        $this->crudPanel->setOperationSetting('showEntryCount', true);
        $this->crudPanel->setOperationSetting('totalEntryCount', 5);

        $this->assertEquals(5, $this->crudPanel->getTotalQueryCount());
    }

    public function testItCanSetTheQueryOnTheCrudPanel()
    {
        $this->assertEquals(User::query()->toSql(), $this->crudPanel->query->toSql());

        $this->crudPanel->setQuery(User::query()->where('id', 1));

        $this->assertEquals(User::query()->where('id', 1)->toSql(), $this->crudPanel->query->toSql());
    }
}
