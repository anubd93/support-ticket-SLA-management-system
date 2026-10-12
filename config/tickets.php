<?php

return [

    'number_prefix' => env('TICKET_NUMBER_PREFIX', 'TKT'),

    'number_padding' => 6,

    'per_page' => 15,

    'attachments' => [
        'disk' => env('ATTACHMENT_DISK', 'local'),
        'max_files' => 5,
        'max_kb' => 5120,
        'mimes' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip'],
    ],

    'import' => [
        'disk' => env('IMPORT_DISK', 'local'),
        'max_kb' => 20480,
        'chunk' => 200,
        'sync_max_rows' => 200,
    ],

];
