<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;
use Backpack\CRUD\ViewNamespaces;
use Config;

/**
 * @covers Backpack\CRUD\ViewNamespaces
 *
 * There is already one registered view namespace `backpack.theme-coreuiv2::` in the `TestsServiceProvider` class.
 */
class ViewNamespacesTest extends BaseCrudPanel
{
    public function testAddSingleViewNamespace()
    {
        ViewNamespaces::addFor('fields', 'crud::fields');
        ViewNamespaces::addFor('fields', 'pro::fields');
        $this->assertCount(3, ViewNamespaces::getFor('fields'));
    }

    public function testAddMultipleViewNamespace()
    {
        ViewNamespaces::addFor('fields', ['crud::fields', 'pro::fields']);
        $this->assertCount(3, ViewNamespaces::getFor('fields'));
    }

    public function testGetWithFallbackFromConfigViewNamespace()
    {
        Config::set('backpack.crud.fallback_namespace', ['fields' => ['fallback::fields']]);
        Config::set('backpack.crud.view_namespaces', ['fields' => ['config::fields']]);
        ViewNamespaces::addFor('fields', ['crud::fields', 'pro::fields']);
        $this->assertCount(5, ViewNamespaces::getWithFallbackFor('fields', 'backpack.crud.fallback_namespace.fields'));
    }

    public function testItCanGetTheViewPathsForGivenElement()
    {
        ViewNamespaces::addFor('fields', ['crud::fields', 'pro::fields']);
        $viewPaths = ViewNamespaces::getViewPathsFor('fields', 'test');
        $this->assertCount(3, $viewPaths);
        $this->assertEquals(['crud::fields.test', 'backpack.theme-coreuiv2::fields.test', 'pro::fields.test'], array_values($viewPaths));
    }
}
