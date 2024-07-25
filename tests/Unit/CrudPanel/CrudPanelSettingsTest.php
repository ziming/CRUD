<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Settings
 */
class CrudPanelSettingsTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel
{
    public function testItCanAddASettingToCrudPanel()
    {
        $this->crudPanel->set('create.test', 'value');

        $this->assertEquals('value', $this->crudPanel->get('create.test'));
    }

    public function testItCanCheckIfASettingExist()
    {
        $this->crudPanel->set('create.test', 'value');

        $this->assertTrue($this->crudPanel->has('create.test'));
        $this->assertFalse($this->crudPanel->has('create.test2'));
    }

    public function testItCanGetOrSetASetting()
    {
        $this->crudPanel->setting('create.test', 'value');

        $this->assertEquals('value', $this->crudPanel->setting('create.test'));
    }

    public function testItCanGetAllSettingOrderedByKey()
    {
        $this->crudPanel->set('create.test', 'value');
        $this->crudPanel->set('create.ambat', 'value2');
        $this->crudPanel->set('ambar.test', 'value3');

        $this->assertEquals([
            'ambar.test' => 'value3',
            'create.test' => 'value',
            'create.ambat' => 'value2',
        ], $this->crudPanel->settings());
    }

    public function testItCanSetOperationSettings()
    {
        $this->crudPanel->setOperation('create');

        $this->crudPanel->setOperationSetting('test', 'value');
        $this->crudPanel->setOperationSetting('test', 'value', 'list');

        $this->assertEquals('value', $this->crudPanel->get('create.test'));
        $this->assertEquals('value', $this->crudPanel->get('list.test'));
    }

    public function testitCanCheckIfOperationSettingsExist()
    {
        $this->crudPanel->setOperation('create');

        $this->crudPanel->setOperationSetting('test', 'value');
        $this->crudPanel->setOperationSetting('test', 'value', 'list');

        $this->assertTrue($this->crudPanel->hasOperationSetting('test'));
        $this->assertTrue($this->crudPanel->hasOperationSetting('test', 'list'));
    }

    public function testItCanSetOperationSettingsForCurrentOperation()
    {
        $this->crudPanel->setOperation('create');

        $this->crudPanel->operationSetting('test', 'value');

        $this->assertEquals('value', $this->crudPanel->get('create.test'));
    }

    public function testItGetsTheOperationListFromSettings()
    {
        $this->crudPanel->set('create.access', 'value');
        $this->crudPanel->set('list.access', 'value2');
        $this->crudPanel->set('something', 'value');
        $this->crudPanel->set('whatever.not', 'value');

        $this->assertEquals(['create', 'list'], $this->invokeMethod($this->crudPanel, 'getAvailableOperationsList'));
    }
}
