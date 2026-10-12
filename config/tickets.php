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

];
