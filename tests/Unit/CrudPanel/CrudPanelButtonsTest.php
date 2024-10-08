<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\CrudButton;
use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Buttons
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudButton
 */
class CrudPanelButtonsTest extends BaseCrudPanel
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
            'content' => 'crud::buttons.show',
            'meta' => [],
        ];
        $this->lineViewButton = [
            'name' => 'lineViewButton',
            'stack' => 'line',
            'type' => 'view',
            'content' => 'crud::buttons.show',
            'position' => null,
            'meta' => [],
        ];
        $this->bottomViewButton = [
            'name' => 'bottomViewButton',
            'stack' => 'bottom',
            'type' => 'view',
            'content' => 'crud::buttons.show',
            'position' => null,
            'meta' => [],
        ];
        $this->topModelFunctionButton = [
            'name' => 'topModelFunctionButton',
            'stack' => 'top',
            'type' => 'model_function',
            'content' => 'crud::buttons.show',
            'position' => null,
            'meta' => [],
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

    public function testOrderButtonsInStack()
    {
        $this->addDefaultButtons();

        $this->crudPanel->orderButtons('top', ['topModelFunctionButton']);

        $this->assertEquals(['topModelFunctionButton', 'topViewButton'], $this->crudPanel->getButtonsForStack('top')->pluck('name')->toArray());
    }

    public function testOrderThrowExceptionIfButtonDoesNotExist()
    {
        $this->addDefaultButtons();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->crudPanel->orderButtons('top', ['unknownButton']);
    }

    public function testAddButtonFluently()
    {
        $button1 = CrudButton::name('lineTest')->to('line')->view('crud::buttons.test')->type('view');
        $button2 = CrudButton::add('modelFunction')->model_function(function () {
            return 'test';
        })->section('top')->makeFirst();
        $this->assertEquals($button1->toArray(), $this->crudPanel->buttons()->last()->toArray());
        $button1->makeLast();
        $this->assertEquals($button2->toArray(), $this->crudPanel->buttons()->first()->toArray());
    }

    public function testItThrowsExceptionWhenModifyingUnknownButton()
    {
        $this->addDefaultButtons();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->crudPanel->modifyButton('unknownButton', function ($button) {
            $button->name = 'newName';
        });
    }

    public function testItCanAddAButtonFromAModelFunction()
    {
        $this->crudPanel->addButtonFromModelFunction('line', 'buttonModelFunction', 'buttonModelFunction');
        $this->assertEquals('buttonModelFunction', $this->crudPanel->buttons()->first()->content);
    }

    public function testItDoesNotMoveFieldWhenTargetIsUnknown()
    {
        $this->addDefaultButtons();

        $firstButtonName = $this->crudPanel->buttons()->first()->name;

        $this->crudPanel->moveButton('unknownButton', 'before', 'topViewButton');

        $this->assertCount(4, $this->crudPanel->buttons());
        $this->assertEquals($firstButtonName, $this->crudPanel->buttons()->first()->name);
    }

    public function testItDoesNotMoveButtonWhenDestinationIsUnknown()
    {
        $this->addDefaultButtons();

        $firstButtonName = $this->crudPanel->buttons()->first()->name;

        $this->crudPanel->moveButton('topViewButton', 'before', 'unknownButton');

        $this->assertCount(4, $this->crudPanel->buttons());
        $this->assertEquals($firstButtonName, $this->crudPanel->buttons()->first()->name);
    }

    public function testItCanCreateANewCrudButtonInstance()
    {
        $button = $this->crudPanel->button(['name' => 'testButton', 'stack' => 'line', 'type' => 'view', 'content' => 'crud::buttons.test']);
        $this->assertEquals($button->toArray(), $this->crudPanel->buttons()->last()->toArray());
        $this->assertInstanceOf(\Backpack\CRUD\app\Library\CrudPanel\CrudButton::class, $button);
    }

    public function testItCanCheckIfAnyOfTheButtonsHasTheDeterminedKayValuePair()
    {
        $this->addDefaultButtons();

        $this->assertTrue($this->crudPanel->hasButtonWhere('name', 'topViewButton'));
        $this->assertFalse($this->crudPanel->hasButtonWhere('name', 'unknownButton'));
    }

    public function testItGenerateARandomButtonNameIfOneNotProvided()
    {
        $button = $this->crudPanel->button(['stack' => 'line', 'type' => 'view', 'content' => 'crud::buttons.test']);
        $this->assertTrue(str_starts_with($button->name, 'button_'));
    }

    public function testMovingTheButtonUsingPosition()
    {
        $button1 = CrudButton::name('lineTest')->to('line')->view('crud::buttons.test')->type('view');
        $button2 = CrudButton::name('lineTest2')->to('line')->view('crud::buttons.test')->type('view')->position('beginning');
        $this->assertEquals($button2->toArray(), $this->crudPanel->buttons()->first()->toArray());
        $button2->position('end');
        $this->assertEquals($button1->toArray(), $this->crudPanel->buttons()->first()->toArray());
    }

    public function testThrowsErrorInUnknownPosition()
    {
        try {
            $button1 = CrudButton::name('lineTest')->to('line')->view('crud::buttons.test')->type('view')->position('unknown');
        } catch (\Throwable $e) {
        }
        $this->assertEquals(
            new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Unknown button position - please use \'beginning\' or \'end\'.', null, ['developer-error-exception']),
            $e
        );
    }

    public function testItCanGetButtonKeyInTheArray()
    {
        $button = CrudButton::make('lineTest')->content('crud::buttons.test')->type('view');
        $this->assertEquals(0, CrudButton::make('lineTest')->getKey());
    }

    public function testItCanRemoveButtonFromButtonList()
    {
        $this->addDefaultButtons();

        CrudButton::make('topViewButton')->remove();

        $this->assertCount(3, $this->crudPanel->buttons());
    }

    public function testItCanAddButtonsToAnHiddenStack()
    {
        $button = CrudButton::make('lineTest')->content('crud::buttons.test')->type('view');
        $this->assertCount(1, $this->crudPanel->getButtonsForStack('hidden'));
    }

    public function testItCanAddMetadataToAButton()
    {
        $button = CrudButton::make('lineTest')->content('crud::buttons.test')->type('view')->meta(['key' => 'value']);
        $this->assertEquals(['key' => 'value'], $this->crudPanel->buttons()->last()->meta);
    }

    public function testItCanForgetAPropertyFromAButton()
    {
        $button = CrudButton::make('lineTest')->content('crud::buttons.test')->type('view')->meta(['key' => 'value'])->group('line');
        $button->forget('meta');
        $this->assertEquals(null, $this->crudPanel->buttons()->last()->meta);
    }

    public function testItCanGetTheButtonHtmlToRender()
    {
        $this->crudPanel->addButtonFromModelFunction('line', 'buttonModelFunction', 'buttonModelFunction');
        $this->assertEquals('model function button test', $this->crudPanel->buttons()->first()->getHtml());

        $this->crudPanel->button('test')->stack('line')->type('view')->content('backpack.theme-coreuiv2::buttons.test');

        $this->assertEquals('<a href="test" class="btn btn-secondary">Test</a>', $this->crudPanel->buttons()->last()->getHtml());
    }

    public function testItThrowsErrorWhenAttemptingToRenderUnknowButtonView()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->crudPanel->button('dontexist')->type('view')->content('unknown_view')->getHtml();
    }

    public function testItThrowsErrorWhenAttemptingToRenderUnknowButtonType()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->crudPanel->button('dontexist')->type('unknown')->getHtml();
    }

    private function getButtonByName($name)
    {
        return $this->crudPanel->buttons()->first(function ($value) use ($name) {
            return $value->name == $name;
        });
    }

    private function addDefaultButtons()
    {
        $this->crudPanel->button($this->topViewButton);
        $this->crudPanel->button($this->lineViewButton);
        $this->crudPanel->button($this->bottomViewButton);
        $this->crudPanel->button($this->topModelFunctionButton);
    }

    private function addTestButton($buttonName)
    {
        $this->crudPanel->button(array_values($this->{$buttonName}));
    }
}
