<?php

return [
    // The directories that will be analyzed to find models.
    'model_directories' => [
        app_path(),
    ],

    'discovered_children_path' => storage_path('app/vendor/parental/discovered_children.php'),
];
