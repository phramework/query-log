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
use \Phramework\Extensions\StepCallback;

/**
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
* @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class QueryLogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Phramework
     */
    private $phramework;

    /**
     * @var QueryLog
     */
    private $queryLog;

    /**
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        //Prepare phramework instance
        $this->phramework = \Phramework\QueryLog\APP\Bootstrap::prepare();

        $settings = \Phramework\QueryLog\APP\Bootstrap::getSettings();

        //Create QueryLog object
        $this->queryLog = new QueryLog($settings);

        $this->queryLog->register();
    }

    /**
     * @covers Phramework\QueryLog\QueryLog::register
     */
    public function testRegister()
    {
        $currentDatabaseAdapter = \Phramework\Database\Database::getAdapter();

        $this->assertInstanceOf(
            'Phramework\QueryLog\QueryLogAdapter',
            $currentDatabaseAdapter
        );
    }

    /**
     * @covers Phramework\QueryLog\QueryLog::register
     */
    public function testRegister2()
    {
        $result = \Phramework\Database\Database::execute('SELECT * FROM user');

        var_dump($result);
    }
}
