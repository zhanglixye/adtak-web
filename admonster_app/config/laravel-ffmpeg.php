<?php

return [
    'default_disk' => 'local',

    'ffmpeg' => [
        'binaries' => env('FFMPEG_BINARIES', '/usr/bin/ffmpeg'),
        'threads' => 12,
    ],

    'ffprobe' => [
        'binaries' => env('FFPROBE_BINARIES', '/usr/bin/ffprobe'),
    ],

    'timeout' => 3600,
];
