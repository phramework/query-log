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

namespace Phramework\QueryLog;

use \Phramework\Phramework;

/**
 * This package wraps phramework's database adapter and logs the executed
 * queries and it's parameters.
 * The presence of this package once activated won't affect the rest of the system.
 * <br/>Defined settings:<br/>
 * <ul>
 * <li>
 *   object database, database settings
 *   <ul>
 *   <li>
 *     string  adapter, IAdapter's implementation class path
 *     Example:
 *     <code>
 *      'adapter' => '\\Phramework\\Database\\MySQL',
 *     </code>
 *   </li>
 *   <li>string  name, Database name</li>
 *   <li>string  username</li>
 *   <li>string  password</li>
 *   <li>string  host</li>
 *   <li>integer port</li>
 *   <li>string  schema, <i>[Optional]</i>, Tables schema, default is null</li>
 *   </ul>
 *   </li>
 * <li>
 *   object  matrix, <i>[Optional]</i>, Log level matrix,
 *   using this matrix queries from specific or all class methods can be
 *   excluded from logging.
 *   The log will be excluded if the above have a non positive value and they
 *   are contained in the current backtrace.
 *   Example:
 *   <code>
 *   'matrix' => (object)[
 *       'MyNamespace\\MyClass' => false,
 *       'MyNamespace\\MySecondClass' => (object)[
 *           'GET' => false
 *       ],
 *   ],
 *   </code>
 * </li>
 * <li>boolean disabled, <i>[Optional]</i>, default is false</li>
 * </ul>
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @example <br/>
 * ```php
 * //Let's assume $settings is global phramework's settings array
 *
 * //... After the use of \Phramework\Database\Database::setAdapter method
 * $queryLog = new \Phramework\QueryLog\QueryLog($settings['query-log']);
 * //Register QueryLog using additional parameters
 * $queryLog->register(['API' => 'base']);
 *
 * //Now phramework instance can be invoked
 * //Execute API
 * $phramework->invoke();
 * ```
 */
class QueryLog
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Create a new query-log instance
     * @param object $settings Settings array
     * @todo Add log level matrix
     * @throws \Exception When database adapter is not set
     * @throws \Phramework\Exceptions\ServerException When `database` setting
     * isn't defined in given settings parameter
     */
    public function __construct($settings)
    {
        if (is_array($settings)) {
            $settings = (object)$settings;
        }

        //Check if system-log setting array is set
        if (!isset($settings->database])) {
            throw new \Phramework\Exceptions\ServerException(
                'database setting is not set for query-log'
            );
        }

        if (!isset($settings->matrix)) {
            $settings->matrix = new \stdClass();
        } elseif (is_array($settings->matrix)) {
            //make sure it's object
            $settings->matrix = (object)$settings->matrix;
        }

        $this->settings = $settings;
    }

    /**
     * Activate query-log
     * @param null|object|array $additionalParameters
     */
    public function register($additionalParameters = null)
    {
        //Ignore if disabled setting is set to true
        if (isset($this->setting['disabled']) && $this->setting['disabled']) {
            return false;
        }

        //Get internal adapter
        $internalAdapter = \Phramework\Database\Database::getAdapter();

        if (!$internalAdapter) {
            throw new \Exception('Database adapter seems to be uset');
        }

        //Create new QueryLogAdapter instance
        $queryLogAdapter = new QueryLogAdapter(
            $this->settings,
            $internalAdapter,
            $additionalParameters
        );

        //Set newly created QueryLogAdapter instance as adapter
        \Phramework\Database\Database::setAdapter($queryLogAdapter);
    }
}
