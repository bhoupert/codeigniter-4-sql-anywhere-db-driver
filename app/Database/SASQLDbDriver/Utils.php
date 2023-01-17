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

namespace CodeIgniter\Database\SASQL;

use CodeIgniter\Database\BaseUtils;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Utils for SASQL
 */
class Utils extends \CodeIgniter\Database\BaseUtils
{
    /**
     * List databases statement
     *
     * @var string
     */
    protected $listDatabases = 'select DB_NAME(Number) from sa_db_list();';

    /**
     * OPTIMIZE TABLE statement
     *
     * @var string
     */
    protected $optimizeTable = 'VALIDATE TABLE %s';

    /**
     * Platform dependent version of the backup function.
     *
     * Based on these examples : https://help.sap.com/docs/SAP_SQL_Anywhere/61ecb3d4d8be4baaa07cc4db0ddb5d0a/812d91936ce21014b77be0a24d8b5438.html
     *
     * @param array|null $prefs
     *                      string  fullOrIncremental
     *                      string  imageOrArchive
     *                      string  directory
     *                      boolean renameTransactionLog
     *                      boolean renameTransactionLogMatch
     * @return mixed
     */
    public function _backup(?array $prefs = null)
    {
        // throw new DatabaseException('Unsupported feature of the database platform you are using.');
        if(!isset($prefs['directory']))
            throw new DatabaseException('Backup directory must be provided');

        //BACKUP DATABASE DIRECTORY 'c:\\temp\\SQLAnybackup';
        $sql = 'BACKUP DATABASE';

        // Image or archive ?
        if (isset($prefs['imageOrArchive']) && strtolower($prefs['imageOrArchive']) == 'image')
            $sql .= ' DIRECTORY';
        else
            $sql .= ' TO';

        $sql .= ' ' . $prefs['directory'];


        // Incremental ?
        if (isset($prefs['renameTransactionLog']) && $prefs['renameTransactionLog'] === true) {
            $sql .= ' TRANSACTION LOG RENAME';
            // Match clause for incremental ?
            // If supplied, the backup copy of the transaction log is given a name of the form YYMMDDnn.log, to match the renamed copy of the current transaction log
            // Using the MATCH keyword enables the same statement to be executed several times without writing over old data.
            if (isset($prefs['renameTransactionLogMatchv']) && $prefs['renameTransactionLogMatch'] === true )
                $sql .= ' MATCH';
        }

        return $this->db->simpleQuery($sql);

    }
}
