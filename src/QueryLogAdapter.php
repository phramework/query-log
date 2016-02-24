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
 * QueryLogAdapter is an implementation of IAdapter which uses an existing
 * adapter to execute the called methods while logging the query in a different adapter.
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
     * Table's schema, null if default is used
     * @var null|string
     */
    protected $schema = null;

    /**
     * Table's name
     * @var string
     */
    protected $table = 'query_log';

    /**
     * Log level matrix
     * @var object
     */
    protected $matrix;

    const LOG_INGORED = 'IGNORED';

    /**
     * @param array                         $settings
     *     Settings array
     * @param \Phramework\Database\IAdapter $internalAdapter
     *     Current database adapter
     * @param null|object|array             $additionalParameters
     *     Additional parameters to store in log
     * @throws Exception
     * @todo Remove typecast to array when log adapters will accept objects
     */
    public function __construct(
        $settings,
        \Phramework\Database\IAdapter $internalAdapter,
        $additionalParameters = null
    ) {
        $logAdapterNamespace = $settings->database->adapter;

        //Initialize new adapter used to store the log queries
        $this->logAdapter = new $logAdapterNamespace(
            (array)$settings->database
        );

        if (!($this->logAdapter instanceof \Phramework\Database\IAdapter)) {
            throw new \Exception(sprintf(
                'Class "%s" is not implementing Phramework\Database\IAdapter',
                $logAdapterNamespace
            ));
        }

        //Check if schema database setting is set
        if (isset($settings->database->schema)) {
            $this->schema = $settings->database->schema;
        }

        //Check if table database setting is set
        if (isset($settings->database->table)) {
            $this->table = $settings->database->table;
        }

        $this->internalAdapter = $internalAdapter;
        $this->additionalParameters = $additionalParameters;

        $this->matrix = $settings->matrix;
    }

    /**
     * Log query to database
     * @param  string  $query
     * @param  array   $parameters
     *     Query parameters
     * @param  integer $startTimestamp
     *     Timestamp before query was executed
     * @param null|Exception $exception
     *     *[Optional]* Exception object if any
     */
    protected function log(
        $query,
        $parameters,
        $startTimestamp,
        $exception = null
    ) {
        $endTimestamp = time();

        $duration = $endTimestamp - $startTimestamp;

        $user = \Phramework\Phramework::getUser();

        $user_id = ($user ? $user->id : null);

        //Get request URI
        list($URI) = \Phramework\URIStrategy\URITemplate::URI();

        //Get request method
        $method = \Phramework\Phramework::getMethod();

        $debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        //Function used by database adapter
        $adapterFunction = $debugBacktrace[1]['function'];

        //Remove current log function call
        //Remove QueryLogAdapter execute* function call
        array_splice($debugBacktrace, 0, 2);

        foreach ($debugBacktrace as $k => &$v) {
            if (isset($v['class'])) {
                $class = $v['class'];
                $function = $v['function'];

                //Check if matrix has an entry for this class
                if (property_exists($this->matrix, $class)) {
                    $matrixEntry = $this->matrix->{$class};

                    if (is_object($matrixEntry) || is_array($matrixEntry)) {
                        //If vector, then is vector contains values for multiple methods of this class

                        //Work with objects
                        if (is_array($matrixEntry)) {
                            $matrixEntry = (object)$matrixEntry;
                        }

                        if (property_exists($matrixEntry, $function)) {
                            //If non positive value, dont log current query
                            if (!$matrixEntry->{$function}) {
                                return self::LOG_INGORED;
                            }
                        }
                    } else {
                        //scalar, this entry has a single value for all methods of this class

                        //If non positive value, dont log current query
                        if (!$matrixEntry) {
                            return self::LOG_INGORED;
                        }
                    }
                }

                $v = $v['class'] . '::' . $v['function'];
            } else {
                $v = $v['function'];
            }
        }

        $schemaTable = (
            $this->schema //if schema is set
            ? '"' . $this->schema . '"."' . $this->table . '"'
            : '"' . $this->table . '"'
        );

        //Insert query log record into table
        return $this->logAdapter->execute(
            'INSERT INTO ' . $schemaTable .
            '(
                "request_id",
                "query",
                "parameters",
                "start_timestamp",
                "duration",
                "function",
                "URI",
                "method",
                "additional_parameters",
                "call_trace",
                "user_id",
                "exception"
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                \Phramework\Phramework::getRequestUUID(),
                $query,
                (
                    $parameters
                    ? json_encode($parameters)
                    : null
                ),
                $startTimestamp,
                $duration,
                $adapterFunction,
                $URI,
                $method,
                (
                    $this->additionalParameters
                    ? json_encode($this->additionalParameters)
                    : null
                ),
                json_encode($debugBacktrace),
                $user_id,
                (
                    $exception
                    ? serialize(QueryLog::flattenExceptionBacktrace($exception))
                    : null
                )
            ]
        );
    }

    /**
     * Get adapter's name
     * @return string Adapter's name
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
     *     Query parameters
     * @return integer Returns the number of rows affected or selected
     * @throws Phramework\Exceptions\DatabaseException
     */
    public function execute($query, $parameters = [])
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->execute($query, $parameters);
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Execute a query and return last instert id
     *
     * @param string $query
     * @param array  $parameters
     *     Query parameters
     * @return integer Returns the id of last inserted record
     * @throws Phramework\Exceptions\DatabaseException
     */
    public function executeLastInsertId($query, $parameters = [])
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->executeLastInsertId(
                $query,
                $parameters
            );
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Execute a query and fetch first row as associative array
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @param array  $castModel
     *     *[Optional]* Default is null, if set then
     * @return array Returns a single row
     * @throws Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetch($query, $parameters = [], $castModel = null)
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->executeAndFetch($query, $parameters);
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Execute a query and fetch all rows as associative array
     *
     * @param string $query
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null
     * @return array[]
     * @throws Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetchAll($query, $parameters = [], $castModel = null)
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->executeAndFetchAll($query, $parameters);
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Execute a query and fetch first row as array
     * @param string $query
     * @param array  $parameters Query parameters
     * @return array
     * @throws Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetchArray($query, $parameters = [])
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->executeAndFetchArray($query, $parameters);
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @return array[]
     * @throws Phramework\Exceptions\DatabaseException
     */
    public function executeAndFetchAllArray($query, $parameters = [])
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->executeAndFetchAllArray($query, $parameters);
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Bind Execute a query and return last instert id
     *
     * @param string $query Query string
     * @param array Query parameters
     * @return mixed
     * @throws Phramework\Exceptions\DatabaseException
     */
    public function bindExecuteLastInsertId($query, $parameters = [])
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->bindExecuteLastInsertId(
                $query,
                $parameters
            );
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Bind Execute a query and return the row count
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @return integer
     * @throws Phramework\Exceptions\DatabaseException
     * @todo provide documentation
     */
    public function bindExecute($query, $parameters = [])
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->bindExecute($query, $parameters);
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

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
     * @throws Phramework\Exceptions\DatabaseException
     */
    public function bindExecuteAndFetch($query, $parameters = [], $castModel = null)
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->bindExecuteAndFetch(
                $query,
                $parameters,
                $castModel
            );
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Bind Execute a query and fetch all rows as associative array
     *
     * @param string $query Query string
     * @param array  $parameters Query parameters
     * @param array $castModel [Optional] Default is null, if set then
     * \Phramework\Models\Filter::castEntry will be applied to data
     * @return array[]
     * @throws Phramework\Exceptions\DatabaseException
     */
    public function bindExecuteAndFetchAll($query, $parameters = [], $castModel = null)
    {
        $startTimestamp = time();

        $exception = null;

        try {
            $result = $this->internalAdapter->bindExecuteAndFetchAll(
                $query,
                $parameters,
                $castModel
            );
        } catch (\Exception $e) {
            $exception = $e;
        } finally {
            //log
            $this->log(
                $query,
                $parameters,
                $startTimestamp,
                $exception
            );

            if ($exception) {
                throw $exception;
            }
        }

        return $result;
    }

    /**
     * Close the connection to database
     */
    public function close()
    {
        return $this->internalAdapter->close();
    }
}
