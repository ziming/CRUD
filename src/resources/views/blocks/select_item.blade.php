<!-- path: resources/views/vendor/backpack/crud/blocks/title.blade.php -->

<?php

// Title Block

$blocks['select_item'] = [
	'type' => 'select_item',
	'label' => 'Select Item',
	'icon'  => 'fa fa-hand-o-up', // TODO: make this icon attribute work
	'fields' => [
		[ // select_entity
            'name' => 'entity_type',
            'label' => "Type",
            'type' => 'select_entity',
            'options' => [
                'article'   => 'Article',
                'category'  => 'Category',
                'tag'       => 'Tag',
            ],
            'model_attributes' => [
                'article'   => 'title',
                'category'  => 'name',
                'tag'       => 'name',
            ],
            'fake' => true,
            'allows_null' => false,
            'default' => 'article',
            'wrapperAttributes' => [
               'class' => 'form-group col-md-3'
             ],
        ],
        [ // select2_entity_item
            'label'                => "Item", // Table column heading
            'type'                 => 'select2_entity_item',
            'name'                 => 'select2_entity_item', // the column that contains the ID of that connected entity;
            'data_source'          => url('api/select-item'), // url to controller search function (with /{id} should return model)
            'item_data_source'     => url('api/select-one-item'),
            'placeholder'          => 'Select an item', // placeholder for the select
            'minimum_input_length' => 2, // minimum characters to type before querying results
            'allows_null'          => true,
            'wrapperAttributes' => [
               'class' => 'form-group col-md-9'
             ],
            'fake' => true,
        ],
		[
			'name' => 'text_block_separator',
			'type' => 'custom_html',
			'value' => '<hr>',
		],
		[
			'name' => 'class',
			'label' => 'Class',
			'type' => 'text',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
		[
			'name' => 'id',
			'label' => 'ID',
			'type' => 'text',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
		[
			'label' => 'Background Color',
			'name' => 'background_color',
			'type' => 'color_picker',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
		[
			'label' => 'Background image',
			'name' => 'background_image',
			'type' => 'browse',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
	],
];

?>