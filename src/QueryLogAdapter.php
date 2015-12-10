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
class QueryLogAdapter implements \Phramework\Database\IAdapter
{
    /**
     * @var \Phramework\Database\IAdapter
     */
    protected $logAdapter;

    /**
     * @var \Phramework\Database\IAdapter
     */
    protected $internalAdapter;

    /**
     * @var null|array|object
     */
    protected $additionalParameters;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @param array                         $settings        Settings array
     * @param \Phramework\Database\IAdapter $internalAdapter Current database adapter
     * @param null|object|array             $additionalParameters Additional parameters to store in log
     */
    public function __construct(
        $settings,
        $internalAdapter,
        $additionalParameters = null
    ) {
        $logAdapterNamespace = $settings['database']['adapter'];

        $this->logAdapter = new $logAdapterNamespace($settings['database']);

        if (!($this->logAdapter instanceof \Phramework\Database\IAdapter)) {
            throw new \Exception(sprintf(
                'Class "%s" is not implementing \Phramework\Database\IAdapter',
                $logAdapterNamespace
            ));
        }

        $this->internalAdapter = $internalAdapter;
        $this->additionalParameters = $additionalParameters;

        $this->uuid = self::generateUUID();
    }

    /**
     * Log query to database
     * @param  string  $query
     * @param  array   $parameters     Query parameters
     * @param  integer $startTimestamp Timestamp before query was executed
     */
    protected function log(
        $query,
        $parameters,
        $startTimestamp
    ) {
        $endTimestamp = time();

        $duration = $endTimestamp - $startTimestamp;

        $user = \Phramework\Phramework::getUser();

        $user_id = ($user ? $user->id : null);

        //Get request URI
        list($URI) = self::URI();

        $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        //Function used by database adapter
        $function = $debugBacktrace[1]['function'];

        //remove this log function call
        //remove QueryLogAdapter execute* function call
        array_splice($debugBacktrace, 0, 2);

        foreach ($debugBacktrace as $k => &$v) {
            $v = $v['class'] . '::' . $v['function'];
        }

        //Insert query log record into "query_log" table
        return $this->logAdapter->execute(
            'INSERT INTO "query_log"
            (
                "request_id",
                "query",
                "parameters",
                "start_timestamp",
                "duration",
                "function",
                "URI",
                "additional_parameters",
                "call_trace",
                "user_id"
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $this->uuid,
                $query,
                ($parameters ? json_encode($parameters) : null),
                $startTimestamp,
                $duration,
                $function,
                $URI,
                (
                    $this->additionalParameters
                    ? json_encode($this->additionalParameters)
                    : null
                ),
                json_encode($debugBacktrace),
                $user_id
            ]
        );
    }

    /**
     * Get adapter's name
     * @return string Adapter's name (lowercase)
     */
    public function getAdapterName()
    {
        return $this->internalAdapter->getAdapterName();
    }

    /**
     * Execute a query and return the row count
     *
     * @param string $query
     * @param array $parameters
     * @return type
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function execute($query, $parameters = [])
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->execute($query, $parameters);

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     * Execute a query and return last instert id
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @return integer Returns the number of rows affected or selected
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeLastInsertId($query, $parameters = [])
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->executeLastInsertId(
            $query,
            $parameters
        );

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     * Execute a query and fetch first row as associative array
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::castEntry will be applied to data
     * @return array Returns a single row
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetch($query, $parameters = [], $castModel = null)
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->execute($query, $parameters = []);

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     * Execute a query and fetch all rows as associative array
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::cast will be applied to data
     * @return array[]
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetchAll($query, $parameters = [], $castModel = null)
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->execute($query, $parameters = []);

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     * Execute a query and fetch first row as array
     * @param string $query
     * @param array  $parameters Query parameters
     * @return type
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetchArray($query, $parameters = [])
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->executeAndFetchArray($query, $parameters);

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @return type
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetchAllArray($query, $parameters = [])
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->executeAndFetchAllArray($query, $parameters);

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     * Bind Execute a query and return last instert id
     *
     * @param string $query Query string
     * @param array Query parameters
     * @return type
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function bindExecuteLastInsertId($query, $parameters = [])
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->bindExecuteLastInsertId(
            $query,
            $parameters
        );

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     * Bind Execute a query and return the row count
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @return type
     * @throws \Phramework\Exceptions\DatabaseException
     * @todo provide documentation
     */
    public function bindExecute($query, $parameters = [])
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->bindExecute($query, $parameters);

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     * Bind Execute a query and fetch first row as associative array
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null, if set
     * then \Phramework\Models\Filter::castEntry will be applied to data
     * @return array
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function bindExecuteAndFetch($query, $parameters = [], $castModel = null)
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->bindExecuteAndFetch(
            $query,
            $parameters,
            $castModel
        );

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     * Bind Execute a query and fetch all rows as associative array
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::castEntry will be applied to data
     * @return type
     * @throws \Phramework\Exceptions\DatabaseException
     */
    public function bindExecuteAndFetchAll($query, $parameters = [], $castModel = null)
    {
        $startTimestamp = time();

        $result = $this->internalAdapter->bindExecuteAndFetchAll(
            $query,
            $parameters,
            $castModel
        );

        //log
        $this->log(
            $query,
            $parameters,
            $startTimestamp
        );

        return $result;
    }

    /**
     * Close the connection to database
     */
    public function close()
    {
        return $this->internalAdapter->close();
    }

    /**
     * Helper method
     * Get current URI and GET parameters from the requested URI
     * @return string[2] Returns an array with current URI and GET parameters
     */
    public static function URI()
    {
        $REDIRECT_QUERY_STRING = (
            isset($_SERVER['QUERY_STRING'])
            ? $_SERVER['QUERY_STRING']
            : ''
        );

        $REDIRECT_URL = '';

        if (isset($_SERVER['REQUEST_URI'])) {
            $url_parts = parse_url($_SERVER['REQUEST_URI']);
            $REDIRECT_URL = $url_parts['path'];
        }

        $URI = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        $URI = '/' . trim(str_replace($URI, '', $REDIRECT_URL), '/');
        $URI = urldecode($URI) . '/';

        $URI = trim($URI, '/');

        $parameters = [];

        //Extract parametrs from QUERY string
        parse_str($REDIRECT_QUERY_STRING, $parameters);

        return [$URI, $parameters];
    }

    /**
     * @return string Returns a 36 characters string
     */
    public static function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
