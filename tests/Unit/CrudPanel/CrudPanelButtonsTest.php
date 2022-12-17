<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\CrudButton;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Buttons
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudButton
 * @covers Backpack\CRUD\app\Library\CrudPanel\Enums\ButtonStackEnum
 * @covers Backpack\CRUD\app\Library\CrudPanel\Enums\ButtonPositionEnum
 */
class CrudPanelButtonsTest extends BaseCrudPanelTest
{
    private $defaultButtonNames = [];

    private $topViewButton;

    private $lineViewButton;

    private $bottomViewButton;

    private $topModelFunctionButton;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setOperation('list');

        $this->topViewButton = [
            'name' => 'topViewButton', 
            'stack' => 'top', 
            'type' => 'view', 
            'content' => 'crud::buttons.show'
        ];
        $this->lineViewButton = [
            'name' => 'lineViewButton', 
            'stack' => 'line', 
            'type' => 'view', 
            'content' => 'crud::buttons.show',
            'position' => null
        ];
        $this->bottomViewButton = [
            'name' => 'bottomViewButton', 
            'stack' => 'bottom', 
            'type' => 'view', 
            'content' => 'crud::buttons.show',
            'position' => null
        ];
        $this->topModelFunctionButton = [
            'name' => 'topModelFunctionButton', 
            'stack' => 'top', 
            'type' => 'model_function', 
            'content' => 'crud::buttons.show',
            'position' => null,
        ];
    }

    public function testItCanAddMultipleButtons()
    {
        $this->addDefaultButtons();

        $this->assertcount(4, $this->crudPanel->buttons());
    }

    public function testCanAddButtonToSpecificStack()
    {
        $this->addDefaultButtons();
        $expectedButton = $this->topViewButton;
        $expectedButton['name'] = 'topViewButtonCustomName';
        $expectedButton['stack'] = 'top';
        $expectedButton['position'] = null;

        $this->crudPanel->addButton($expectedButton['stack'], $expectedButton['name'], $expectedButton['type'], $expectedButton['content']);

        $this->assertEquals($expectedButton, $this->crudPanel->buttons()->last()->toArray());
        $this->assertCount(3, $this->crudPanel->getButtonsForStack($expectedButton['stack']));
    }

    public function testAddButtonBottomUnknownStackName()
    {
        $this->expectException(\Exception::class);

        $expectedButton = $this->topViewButton;

        $this->crudPanel->addButton('unknownStackName', $expectedButton['name'], $expectedButton['type'], $expectedButton['content']);
    }

    public function testAddButtonsWithSameName()
    {
        $expectedButton = $this->topViewButton;

        $this->crudPanel->addButton($expectedButton['stack'], $expectedButton['name'], $expectedButton['type'], $expectedButton['content']);
        $this->crudPanel->addButton($expectedButton['stack'], $expectedButton['name'], $expectedButton['type'], $expectedButton['content']);

        $this->assertCount(1, $this->crudPanel->buttons());

        $expectedButton2 = $this->bottomViewButton;
        CrudButton::name($expectedButton2);
        CrudButton::name($expectedButton2);

        $this->assertCount(2, $this->crudPanel->buttons());
    }

    public function testAddButtonsWithSameNameWithoutReplacing()
    {
        $this->markTestIncomplete('This no longer makes sense in Backpack 4.1. Button names are unique now.');

        $expectedButton = $this->topViewButton;

        $this->crudPanel->addButton($expectedButton['stack'], $expectedButton['name'], $expectedButton['type'], $expectedButton['content'], false, false);
        $this->crudPanel->addButton($expectedButton['stack'], $expectedButton['name'], $expectedButton['type'], $expectedButton['content'], false, false);

        $this->assertEquals(count($this->defaultButtonNames) + 2, count($this->crudPanel->buttons()));
    }

    public function testAddButtonBeginning()
    {
        $this->addTestButton('topViewButton');

        $expectedButton = $this->bottomViewButton;

        $this->crudPanel->addButton($expectedButton['stack'], $expectedButton['name'], $expectedButton['type'], $expectedButton['content'], 'beginning');

        $this->assertEquals($expectedButton, $this->crudPanel->buttons()->first()->toArray());
    }

    public function testAddButtonEnd()
    {
        
        $this->addTestButton('lineViewButton');

        $expectedButton = $this->lineViewButton;

        $this->crudPanel->addButton($expectedButton['stack'], $expectedButton['name'], $expectedButton['type'], $expectedButton['content'], 'end');

        $this->assertEquals($expectedButton, $this->crudPanel->buttons()->last()->toArray());
    }

    public function testAddButtonUnknownPosition()
    {

        $this->expectException(\Exception::class);

        $expectedButton = $this->lineViewButton;

        $this->crudPanel->addButton($expectedButton['stack'], $expectedButton['name'], $expectedButton['type'], $expectedButton['content'], 'unknownPosition');
    }

    public function testAddButtonFromModelFunction()
    {
        $expectedButton = $this->topModelFunctionButton;

        $this->crudPanel->addButton($expectedButton['stack'], $expectedButton['name'], $expectedButton['type'], $expectedButton['content']);

        $this->assertEquals($expectedButton, $this->crudPanel->buttons()->last()->toArray());
    }

    public function testAddButtonFromView()
    {
        $expectedButton = $this->topViewButton;
        $viewName = 'someViewName';

        $this->crudPanel->addButtonFromView($expectedButton['stack'], $expectedButton['name'], $viewName);

        $backpackButtonViewPackage = 'crud::buttons.';
        $actualButton = $this->crudPanel->buttons()->last();

        $this->assertEquals($expectedButton['stack'], $actualButton->stack);
        $this->assertEquals($expectedButton['name'], $actualButton->name);
        $this->assertEquals($backpackButtonViewPackage.$viewName, $actualButton->content);
    }

    public function testRemoveButton()
    {
        $this->crudPanel->addButton('line', 'update', 'view', 'crud::buttons.update', 'end');
        $this->crudPanel->removeButton('update');

        $this->assertCount(0, $this->crudPanel->buttons());
        $this->assertNull($this->getButtonByName('update'));
    }
    
    public function testRemoveButtons()
    {
        $this->crudPanel->addButton('line', 'update', 'view', 'crud::buttons.update', 'end');
        $this->crudPanel->addButton('line', 'show', 'view', 'crud::buttons.show', 'end');
        $this->crudPanel->removeButtons(['show', 'update']);

        $this->assertCount(0, $this->crudPanel->buttons());
        $this->assertNull($this->getButtonByName('show'));
        $this->assertNull($this->getButtonByName('update'));
    }
     
    public function testRemoveUnknownButtons()
    {
        $buttonNames = [
            'someButtonName',
            'someOtherButtonName',
        ];

        $this->addDefaultButtons();
        $this->crudPanel->removeButtons($buttonNames);

        $this->assertCount(4, $this->crudPanel->buttons());
    }
     
    public function testRemoveUnknownButton()
    {

        $this->addTestButton('topViewButton');

        $this->crudPanel->removeButton('someButtonName');

        $this->assertCount(1, $this->crudPanel->buttons());
    }
     
    public function testRemoveAllButtons()
    {
        $this->addDefaultButtons();
        $this->crudPanel->removeAllButtons();

        $this->assertEmpty($this->crudPanel->buttons());
    }
     
    public function testRemoveButtonFromStack()
    {
        $this->crudPanel->addButton('line', 'update', 'view', 'crud::buttons.update', 'end');

        $button = $this->crudPanel->buttons()->first();

        $this->crudPanel->removeButtonFromStack($button->name, $button->stack);

        $this->assertCount(0, $this->crudPanel->buttons());
        $this->assertNull($this->getButtonByName($button->name));
    }
    
    public function testRemoveUnknownButtonFromStack()
    {   
        $this->addTestButton('lineViewButton');
        $this->crudPanel->removeButtonFromStack('someButtonName', 'line');

        $this->assertCount(1, $this->crudPanel->buttons());
    }
    
    public function testRemoveButtonFromUnknownStack()
    {
        $this->crudPanel->addButton('line', 'update', 'view', 'crud::buttons.update', 'end');
        $this->crudPanel->addButton('line', 'show', 'view', 'crud::buttons.show', 'end');

        $button = $this->crudPanel->buttons()->first();

        $this->crudPanel->removeButtonFromStack($button->name, 'someStackName');

        $this->assertCount(2, $this->crudPanel->buttons());
    }
     
    public function testRemoveAllButtonsFromStack()
    {
        $this->crudPanel->addButton('line', 'update', 'view', 'crud::buttons.update', 'end');
        $this->crudPanel->addButton('line', 'show', 'view', 'crud::buttons.show', 'end');

        $this->crudPanel->removeAllButtonsFromStack('line');

        $this->assertCount(0, $this->crudPanel->buttons());
    }
     
    public function testRemoveAllButtonsFromUnknownStack()
    {
        $this->addTestButton('lineViewButton');

        $this->crudPanel->removeAllButtonsFromStack('someStackName');

        $this->assertCount(1, $this->crudPanel->buttons());
    }
    
    public function testOrderButtons()
    {
        $this->crudPanel->addButton('line', 'update', 'view', 'crud::buttons.update', 'end');
        $this->crudPanel->addButton('line', 'show', 'view', 'crud::buttons.show', 'end');
        $this->crudPanel->addButton('line', 'test', 'view', 'crud::buttons.test', 'end');

        $this->crudPanel->orderButtons('line', ['show', 'test']);

        $this->assertEquals(['show', 'test', 'update'], $this->crudPanel->buttons()->pluck('name')->toArray());
    }

    public function testAddButtonFluently()
    {
        $button1 = CrudButton::name('lineTest')->to('line')->view('crud::buttons.test')->type('view');
        $button2 = CrudButton::add('modelFunction')->model_function(function() {
            return 'test';
        })->section('top')->makeFirst();
        $this->assertEquals($button1->toArray(), $this->crudPanel->buttons()->last()->toArray());
        $button1->makeLast();
        $this->assertEquals($button2->toArray(), $this->crudPanel->buttons()->first()->toArray());
    }
    
    private function getButtonByName($name)
    {
        return $this->crudPanel->buttons()->first(function ($value) use ($name) {
            return $value->name == $name;
        });
    }

    private function addDefaultButtons()
    {
        CrudButton::name($this->topViewButton);
        CrudButton::name($this->lineViewButton);
        CrudButton::name($this->bottomViewButton);
        CrudButton::name($this->topModelFunctionButton);
    }

    private function addTestButton($buttonName)
    {
        CrudButton::name(array_values($this->{$buttonName}));
    }
}
