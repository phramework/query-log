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
 *   array database <ul>
 *   <li>string  adapter, IAdapter's implementation classpath</li>
 *   <li>string  name, Database name</li>
 *   <li>string  username</li>
 *   <li>string  password</li>
 *   <li>string  host</li>
 *   <li>integer port</li>
 *   <li>string schema, <i>[Optional]</i>, Tables schema, default is null</li>
 *  </ul>
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
     * @param array $settings Settings array
     * @todo Add log level matrix
     */
    public function __construct($settings)
    {

        //Check if system-log setting array is set
        if (!isset($settings['database'])) {
            throw new \Phramework\Exceptions\ServerException(
                'database setting is not set for query-log'
            );
        }

        $this->settings = $settings;
    }

    /**
     * Activate query-log
     * @param  null|object|array $additionalParameters
     */
    public function register($additionalParameters = null)
    {
        //Ignore if disabled setting is set to true
        if (isset($this->setting['disabled']) && $this->setting['disabled']) {
            return false;
        }

        //Get internal adapter
        $internalAdapter = \Phramework\Database\Database::getAdapter();

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
