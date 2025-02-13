<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\TranslatableModel;

/**
 * @covers Backpack\CRUD\app\Models\Traits\HasTranslatableFields
 */
class TranslatableFieldsTest extends BaseDBCrudPanel
{
    /**
     * Setup function for each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setModel(TranslatableModel::class);

        $this->crudPanel->field('title');
        $this->crudPanel->field('description');
    }

    public function testFieldsGetsTranslated()
    {
        $this->crudPanel->create([
            'title' => 'english title',
            'description' => 'english description',
        ]);

        $model = TranslatableModel::first();

        $this->assertEquals('english title', $model->title);

        config(['app.locale' => 'fr']);

        $this->assertEquals('english title', $model->title);

        $this->crudPanel->update(1, ['title' => 'french title']);

        $model->refresh();

        $this->assertEquals('french title', $model->title);
    }
}
