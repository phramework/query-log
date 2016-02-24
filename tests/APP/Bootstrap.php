<?php
/**
 * Copyright 2015 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Phramework\QueryLog\APP;

use \Phramework\Phramework;

/**
 * Log implementation for PHPUnit tests
 * Use setCallback to register a callback and write PHPUnit tests inside
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class Bootstrap
{
    public static function getSettings()
    {
        $settings = [
            'debug' => true,
            'database' => [
                'adapter' => 'Phramework\\Database\\MySQL',
                'host' => '',
                'username' => '',
                'password' => '',
                'name' => '',
                'port' => 3306
            ],
            'query-log' => (object)[
                'disabled' => false,
                'database' => (object)[
                    'adapter' => 'Phramework\\Database\\MySQL',
                    'host' => '',
                    'username' => '',
                    'password' => '',
                    'name' => '',
                    'port' => 3306,
                    'table' => 'query_log'
                ],
                //alternative configuration (will replace database manualy by postgresql tests)
                'postgresql' => (object)[
                    'adapter' => 'Phramework\\Database\\PostgreSQL',
                    'host' => '',
                    'username' => '',
                    'password' => '',
                    'name' => '',
                    'port' => 5432
                ],
                'matrix' => (object) [
                    'Phramework\\SystemLog\\APP\\Controllers\\DummyController' => (object) [
                        'GET' => false,
                        'POST' => true
                    ],
                    'Phramework\\QueryLog\\APP\\Models\\User' => (object) [
                        'get' => true,
                        'getById' => false,
                        'post' => true,
                    ],
                    //'Phramework\\SystemLog\\APP\\Controllers\\DummyController' => false
                ]
            ]
        ];

        if (file_exists(__DIR__.'/localsettings.php')) {
            include __DIR__.'/localsettings.php';
        }

        return $settings;
    }

    /**
     * Prepare a phramework instance
     * @uses Bootstrap::getSettings() to fetch the settings
     * @return Phramework
     */
    public static function prepare()
    {
        $settings = self::getSettings();

        $phramework = new Phramework(
            $settings,
            new \Phramework\URIStrategy\URITemplate([
                [
                    '/',
                    '\\Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GET',
                    Phramework::METHOD_ANY
                ],
                [
                    '/dummy/',
                    '\\Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GET',
                    Phramework::METHOD_ANY
                ],
                [
                    '/dummy/{id}',
                    '\\Phramework\\SystemLog\\APP\\Controllers\\DummyController',
                    'GETById',
                    Phramework::METHOD_ANY
                ],
            ])
        );

        \Phramework\Database\Database::setAdapter(
            new \Phramework\Database\MySQL($settings['database'])
        );

        return $phramework;
    }
}
