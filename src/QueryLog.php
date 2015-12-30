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
 * This package wraps phramework's database adapter and keeps logs of the executed
 * queries and it's parameters.
 * The presence of this package once activated won't affect the rest of the system.
 * <br/><b>Defined settings:</b><br/>
 * <ul>
 * <li>boolean disabled, <i>[Optional]</i>, default is false</li>
 * <li>
 *     object database, database settings
 *   <ul>
 *   <li>
 *     string  adapter, IAdapter's implementation class path
 *     <div class="alert alert-info">
 *     <i>Example:</i>
 *     <code>
 *     'adapter' => '\\Phramework\\Database\\MySQL',
 *     </code>
 *     </div>
 *   </li>
 *   <li>string  name, Database name</li>
 *   <li>string  username</li>
 *   <li>string  password</li>
 *   <li>string  host</li>
 *   <li>integer port</li>
 *   <li>string  schema, <i>[Optional]</i>, Tables schema, default is null</li>
 *   </ul>
 * </li>
 * <li>
 *   object  matrix, <i>[Optional]</i>, Log level matrix,
 *   using this matrix queries from specific or all class methods can be
 *   excluded from logging.
 *   The log will be excluded if the above have a non positive value and they
 *   are contained in the current backtrace.
 *   <div class="alert alert-info">
 *   <i>Example:</i>
 *   <code>
 *   'matrix' => (object)[
 *       'MyNamespace\\MyClass' => false,
 *       'MyNamespace\\MySecondClass' => (object)[
 *           'GET' => false
 *       ],
 *   ],
 *   </code>
 *   </div>
 * </li>
 * </ul>
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 * @example <br/>
 * ```php
 * //Let's assume $settings is global phramework's settings array
 *
 * //... After the use of \Phramework\Database\Database::setAdapter method,
 * //the global adapter is initialized
 *
 * //Now we can create a QueryLog object
 * $queryLog = new \Phramework\QueryLog\QueryLog($settings['query-log']);
 *
 * //And register this object, using additional parameters.
 * $queryLog->register(['API' => 'base']);
 *
 * //Now we can invoke phramework's instance
 *
 * //Queries executed from this point are now logged based on the provided rules
 *
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

        //Check if database is set at system-log settings
        if (!isset($settings->database])) {
            throw new \Phramework\Exceptions\ServerException(
                '"database" setting is not set for query-log'
            );
        }

        //Make sure matrix setting is set
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
     *     Additional parameters to be stored in query logs
     * @return boolean Returns false if query log is disabled
     */
    public function register($additionalParameters = null)
    {
        //Ignore registration if disabled setting is set to true
        if (isset($this->setting->disabled) && $this->setting->disabled) {
            return false;
        }

        //Get current global adapter
        $internalAdapter = \Phramework\Database\Database::getAdapter();

        if (!$internalAdapter) {
            throw new \Exception('Global database adapter is not initialized');
        }

        //Create new QueryLogAdapter instance, using current global adapter
        $queryLogAdapter = new QueryLogAdapter(
            $this->settings,
            $internalAdapter,
            $additionalParameters
        );

        //Set newly created QueryLogAdapter instance as global adapter
        \Phramework\Database\Database::setAdapter($queryLogAdapter);

        return true;
    }
}
