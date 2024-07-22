<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudObjectGroup
 */
class CrudPanelObjectGroupTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel
{
    public function testItCanCreateAGroupOfCrudObjects()
    {
        $this->crudPanel->group(
            $this->crudPanel->field('test'),
            $this->crudPanel->field('test2')
        )->label('testing');

        $this->assertEquals('testing', $this->crudPanel->fields()['test']['label']);
        $this->assertEquals('testing', $this->crudPanel->fields()['test2']['label']);
    }

    public function testItCanCreateAGroupOfCrudObjectsFromArrayInput()
    {
        $this->crudPanel->group([
            $this->crudPanel->field('test'),
            $this->crudPanel->field('test2'),
        ])->label('testing');

        $this->assertEquals('testing', $this->crudPanel->fields()['test']['label']);
        $this->assertEquals('testing', $this->crudPanel->fields()['test2']['label']);
    }
}
