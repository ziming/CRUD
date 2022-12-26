<?php

return [
    'class' => [
        'header' => 'app-header bg-light border-0 navbar',
        'body' => 'app aside-menu-fixed sidebar-lg-show',
        'sidebar' => 'sidebar sidebar-pills bg-light',
        'footer' => 'app-footer d-print-none',
    ],

    /**
     * Styles
     */
    'styles' => [
        'https://unpkg.com/@coreui/coreui@2.1.16/dist/css/coreui.min.css',
        'https://unpkg.com/animate.css@4.1.1/animate.compat.css',
        'https://unpkg.com/noty@3.2.0-beta-deprecated/lib/noty.css',

        ['https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,400;0,600;0,700;1,400&display=swap', true, [], 'style'],

        'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-regular-400.woff2',
        'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-solid-900.woff2',
        'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-brands-400.woff2',
    ],

    /**
     * Scripts
     */
    'scripts' => [
        'https://unpkg.com/jquery@3.6.1/dist/jquery.min.js',
        'https://unpkg.com/popper.js@1.16.1/dist/popper.min.js',
        'https://unpkg.com/noty@3.2.0-beta-deprecated/lib/noty.min.js',
        'https://unpkg.com/bootstrap@4.6.0/dist/js/bootstrap.min.js',
        'https://unpkg.com/@coreui/coreui@2.1.16/dist/js/coreui.min.js',
        'https://unpkg.com/pace-js@1.2.4/pace.min.js',
        'https://unpkg.com/sweetalert@2.1.2/dist/sweetalert.min.js',
    ],
];
