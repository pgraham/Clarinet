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
class PdoExceptionWrapper extends Exception {

  const MSG_RE = '/SQLSTATE\[([\d\w]{5})\]: (.+)/';
  const ERROR_MSG_RE = '/(.+):\s*(\d+)\s*(.+)$/';

  private $_sqlState;
  private $_sqlMsg;

  private $_mysqlClass;
  private $_mysqlCode;
  private $_mysqlMsg;

  /**
   * Parse information that can be usedto create an exception with a user
   * readable error message.
   *
   *
   * @param PDOException $pdoe
   */
  public function __construct(PDOException $pdoe) {
    parent::__construct();

    $this->_parseMessage($pdoe->getMessage());
  }

  public function getSqlState() {
    return $this->_sqlState;
  }

  public function getSqlMsg() {
    return $this->_sqlMsg;
  }

  public function getMysqlClass() {
    return $this->_mysqlClass;
  }

  public function getMysqlCode() {
    return $this->_mysqlCode;
  }

  public function getMysqlMsg() {
    return $this->_mysqlMsg;
  }

  private function _parseMessage($msg) {
    if (preg_match(self::MSG_RE, $msg, $matches)) {
      $this->_sqlState = $matches[1];
      $this->_sqlMsg = $matches[2];

      if (preg_match(self::ERROR_MSG_RE, $this->_sqlMsg, $matches)) {
        $this->_mysqlClass = $matches[1];
        $this->_mysqlCode = $matches[2];
        $this->_mysqlMsg = $matches[3];
      }
    }
  }
}
