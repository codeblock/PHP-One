<?php
/**
 * @author beanfondue@gmail.com
 * @see
 *     const CONSTANTS = [
 *         label1 => [properties],
 *         label2 => [properties],
 *         labeln => ...
 *     ]
 */

const FDEF_DB_CONN = [
    'user' => [
        'kind' => 'mysql',
        'host' => '${host}',
        'port' => 3306,
        'user' => '${user}',
        'pass' => '${pass}',
        'name' => '${name}',
        'wait' => 0 // 1.0 / ...
    ],
    'log' => [
        'kind' => 'mysql',
        'host' => '${host}',
        'port' => 3307,
        'user' => '${user}',
        'pass' => '${pass}',
        'name' => '${name}',
        'wait' => 0
    ]
];

const FDEF_CACHE_CONN = [
    'user' => [
        'kind' => 'redis',
        'host' => '${host}',
        'port' => 6379,
        'user' => null,
        'pass' => '${pass}',
        //'name' => 0, // 1 / ...
        'wait' => 0.0 // 1.0 / ...
    ]
];