<?php

/**
 * This file is part of codeigniter-4-sql-anywhere-db-driver.
 *
 * (c) Baptiste HOUPERT <houpert.baptiste@free.fr>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Database\SASQLDbDriver;

use BadMethodCallException;
use CodeIgniter\Database\BasePreparedQuery;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Prepared query for Postgre
 *
 * @extends BasePreparedQuery<resource, resource, resource>
 */
class PreparedQuery extends \CodeIgniter\Database\BasePreparedQuery
{
    /**
     * Parameters array used to store the dynamic variables.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * A reference to the db connection to use.
     *
     * @var Connection
     */
    protected $db;

    public function __construct(Connection $db)
    {
        parent::__construct($db);
    }

    /**
     * Prepares the query against the database, and saves the connection
     * info necessary to execute the query later.
     *
     * NOTE: This version is based on SQL code. Child classes should
     * override this method.
     *
     * @param array $options Options takes an associative array;
     *
     * @throws DatabaseException
     */
    public function _prepare(string $sql, array $options = []): PreparedQuery
    {
        $this->statement = sasql_prepare($this->db->connID, $sql);

        if (! $this->statement) {
            if ($this->db->DBDebug) {
                throw new DatabaseException($this->db->getAllErrorMessages());
            }

            $info              = $this->db->error();
            $this->errorCode   = $info['code'];
            $this->errorString = $info['message'];
        }

        return $this;
    }

    /**
     * Takes a new set of data and runs it against the currently
     * prepared query.
     */
    public function _execute(array $data): bool
    {
        if (! isset($this->statement)) {
            throw new BadMethodCallException('You must call prepare before trying to execute a prepared statement.');
        }

        foreach ($data as $key => $value) {
            $this->parameters[$key] = $value;
        }

        // Binding each parameter
        $paramNumber = 0;
        foreach ($this->parameters as &$value) {
            sasql_stmt_bind_param_ex(
                $this->statement,
                $paramNumber++,
                $value,
                $value ? gettype($value)[0] : 's',
                $value === null
            );
        }

        $result = sasql_stmt_execute($this->statement);

        if ($result === false && $this->db->DBDebug) {
            throw new DatabaseException($this->db->getAllErrorMessages());
        }

        return $result;
    }

    /**
     * Returns the statement resource for the prepared query or false when preparing failed.
     *
     * @return resource|null
     */
    public function _getResult()
    {
        return $this->statement;
    }

    /**
     * Deallocate prepared statements.
     */
    protected function _close(): bool
    {
        return sasql_stmt_free_result($this->statement);
    }

    /**
     * Handle parameters.
     */
    protected function parameterize(string $queryString): array
    {
        $numberOfVariables = substr_count($queryString, '?');

        $params = [];

        for ($c = 0; $c < $numberOfVariables; $c++) {
            $this->parameters[$c] = null;
            $params[]             = &$this->parameters[$c];
        }

        return $params;
    }
}
