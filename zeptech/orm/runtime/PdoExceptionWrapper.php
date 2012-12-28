<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zeptech\orm\runtime;

use \Exception;
use \PDOException;

/**
 * This class parses the messages and codes in a given PDOException object in
 * order to generate an error message that is suitable for presenting to users.
 *
 * TODO Implement driver specific message parsing
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PdoExceptionWrapper extends Exception
{

    const MSG_RE = '/SQLSTATE\[([\d\w]{5})\]: (.+)/';
    const ERROR_MSG_RE = '/(.+):\s*(\d+)\s*(.+)$/';

    private $sql;

    private $sqlState;
    private $sqlMsg;

    private $mysqlClass;
    private $mysqlCode;
    private $mysqlMsg;

    /**
     * Parse information that can be usedto create an exception with a user
     * readable error message.
     *
     *
     * @param PDOException $pdoe
     */
    public function __construct(PDOException $pdoe, $sql)
    {
        parent::__construct($pdoe->getMessage(), $pdoe->getCode(), $pdoe);

        $this->_parseMessage($pdoe->getMessage());
        $this->sql = $sql;
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function getSqlState()
    {
        return $this->sqlState;
    }

    public function getSqlMsg()
    {
        return $this->sqlMsg;
    }

    public function getMysqlClass()
    {
        return $this->mysqlClass;
    }

    public function getMysqlCode()
    {
        return $this->mysqlCode;
    }

    public function getMysqlMsg()
    {
        return $this->mysqlMsg;
    }

    private function _parseMessage($msg)
    {
        if (preg_match(self::MSG_RE, $msg, $matches)) {
            $this->sqlState = $matches[1];
            $this->sqlMsg = $matches[2];

            if (preg_match(self::ERROR_MSG_RE, $this->sqlMsg, $matches)) {
                $this->mysqlClass = $matches[1];
                $this->mysqlCode = $matches[2];
                $this->mysqlMsg = $matches[3];
            }
        }
    }
}
