<?php
/**
 * Created by PhpStorm.
 * User: gb
 * Date: 2018-12-25
 * Time: 09:53
 */

return [
    'elasticsearch' => [
        'prefix' => env('ELASTICSEARCH_PREFIX', 'laravel_'),
        'hosts' => [
            env('ELASTICSEARCH_HOST', 'http://localhost'),
        ],
        'analyzer' => env('ELASTICSEARCH_ANALYZER', 'ik_max_word'),
        'settings' => [],
        'filter' => [
            '+',
            '-',
            '&',
            '|',
            '!',
            '(',
            ')',
            '{',
            '}',
            '[',
            ']',
            '^',
            '\\',
            '"',
            '~',
            '*',
            '?',
            ':'
        ]
    ],
];