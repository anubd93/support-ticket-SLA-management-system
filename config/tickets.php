<?php

return [

    /*
    | Ticket numbers look like TKT-2026-000001: prefix, year, then a per-year
    | counter padded to this many digits (it simply grows past the padding).
    */
    'number_prefix' => env('TICKET_NUMBER_PREFIX', 'TKT'),

    'number_padding' => 6,

    'per_page' => 15,

];
