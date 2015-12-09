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
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
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
