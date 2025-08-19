<?php

return [
    /*
    |--------------------------------------------------------------------------
    | '${short_name}' Plugin Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the '${short_name}' plugin.
    | You can modify these values to customize the plugin behavior.
    |
    */
    
    'enabled' => env(''${short_name^^}_ENABLED'', true),
    
    'default_settings' => [
        'theme' => 'light',
        'language' => 'en',
    ],
    
    'permissions' => [
        'view' => 'view-'${short_name}',
        'delete' => 'delete-'${short_name}',
    ],
];
