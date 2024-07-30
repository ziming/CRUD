<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Reorder
 */
class CrudPanelReorderTest extends BaseCrudPanel
{
    public function testEnableReorder()
    {
        $this->crudPanel->enableReorder();

        $this->assertTrue($this->crudPanel->getOperationSetting('enabled'));
        $this->assertEquals('name', $this->crudPanel->getOperationSetting('label'));
        $this->assertEquals(1, $this->crudPanel->getOperationSetting('max_level'));
    }

    public function testDisableReorder()
    {
        $this->crudPanel->enableReorder();

        $this->assertTrue($this->crudPanel->isReorderEnabled());

        $this->crudPanel->disableReorder();

        $this->assertFalse($this->crudPanel->isReorderEnabled());
    }
}
