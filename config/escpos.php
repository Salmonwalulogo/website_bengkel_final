<?php

return [
    'driver' => env('ESCPOS_DRIVER', 'browser'),
    'windows_queue' => env('ESCPOS_WINDOWS_QUEUE', 'POS80-NEWW'),
];
