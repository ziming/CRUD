<!-- path: resources/views/vendor/backpack/crud/blocks/press.blade.php -->

<?php

// Press Block

$blocks['press'] = [
	'type' => 'press',
	'label' => 'Press',
	'icon'  => 'fa fa-newspaper-o',
	'fields' => [
		[
			'name' => 'title',
			'label' => 'Title',
			'type' => 'text',
		],
		[
			'name' => 'description',
			'label' => 'Description',
			'type' => 'textarea',
		],
		[
			'name' => 'thumbnail',
			'label' => 'Thumbnail',
			'type' => 'browse',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
		[
			'name' => 'pdf',
			'label' => 'PDF file',
			'type' => 'browse',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
		[
			'name' => 'text_block_separator',
			'type' => 'custom_html',
			'value' => '<hr>',
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
		[
            'type'  => 'keepeek',
            'name'  => 'another_image',
            'label' => 'Another Keepeek Image',
            'label_button' => 'Select another file',
            // Map media types to keepeek links depending on size.
            // The keepeek field will check if the media type startsWith the key given, then go down the list until it finds a match. If there is no match, 'kpk:preview' is used.
            'sizes' => [
                'image' => 'kpk:2_3_medium',
                'video' => 'kpk:720p',
                'text/html' => 'kpk:hires',
            ],
            'url_controller' => route('keepeek_bridge'),
        ]
	],
];

?>