<?php

return [
    'driver' => env('ESCPOS_DRIVER', PHP_OS_FAMILY === 'Windows' ? 'windows' : 'browser'),
    'windows_queue' => env('ESCPOS_WINDOWS_QUEUE', 'POS80-NEWW'),
];
