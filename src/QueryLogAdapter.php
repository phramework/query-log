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
class QueryLog implements \Phramework\Database\IAdapter
{
    private $logAdapter;

    protected function log($method, $query, $parameters)
    {
        //
        $logAdapter->execute()
    }

    /**
     * @var \Phramework\Database\IAdapter
     */
    private $internalAdapter;

    public function __construct($settings, $internalAdapter)
    {
        $logAdapterNamespace = $settings['query-log']['database']['adapter'];

        $this->logAdapter = new $logAdapterNamespace();

        if (!($this->logAdapter instanceof \Phramework\Database\IAdapter)) {
            throw new \Exception(sprintf(
                'Class "%s" is not implementing \Phramework\Database\IAdapter',
                $logAdapterNamespace
            ));
        }

        $this->internalAdapter = $internalAdapter;
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
        $result = $this->$internalAdapter->execute($query, $parameters);

        //log
        $this->log(__METHOD__, $query, $parameters)

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
        $result = $this->$internalAdapter->executeLastInsertId(
            $query,
            $parameters
        );

        //log

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
        $result = $this->$internalAdapter->execute($query, $parameters = []);

        //log

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
        $result = $this->$internalAdapter->execute($query, $parameters = []);

        //log

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
        $result = $this->$internalAdapter->executeAndFetchArray($query, $parameters);

        //log

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
        $result = $this->$internalAdapter->executeAndFetchAllArray($query, $parameters);

        //log

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
        $result = $this->$internalAdapter->bindExecuteLastInsertId(
            $query,
            $parameters
        );

        //log

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
        $result = $this->$internalAdapter->bindExecute($query, $parameters);

        //log

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
        $result = $this->$internalAdapter->bindExecuteAndFetch(
            $query,
            $parameters,
            $castModel
        );

        //log

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
        $result = $this->$internalAdapter->bindExecuteAndFetchAll(
            $query,
            $parameters,
            $castModel
        );

        //log

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
