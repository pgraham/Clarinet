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
namespace zpt\orm;

use \PDO;
use \Exception;

/**
 * This class decorates a PDO connection.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PdoWrapper {

  /* PdoWrapper instance being used by the library */
  private static $_instance;

  /**
   * Get the PdoWrapper instance.  If a PDO connection has not yet been
   * specified by using PdoWrapper::set($pdo) this method will throw an
   * exception.
   *
   * @return PdoWrapper
   */
  public static function get() {
    if (self::$_instance === null) {
      throw new Exception("No PDO connection has been specified");
    }
    return self::$_instance;
  }

  /**
   * Set the PDO connection to wrap.  If a connection is already specified, it
   * will be closed.
   *
   * @param PDO The PDO connection to decorate.
   */
  public static function set(PDO &$pdo) {
    self::$_instance = new PdoWrapper($pdo);
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  /* The wrapped PDO connection */
  private $_pdo;

  /* Whether or not a transaction is in progress */
  private $_inTransaction = false;

  /**
   * Create a new PdoWrapper.
   *
   * @param PDO $pdo The PDO connection to wrap.
   */
  private function __construct(PDO &$pdo) {
    $this->_pdo =& $pdo;
  }

  /**
   * Passthrough for the beginTransaction() method.
   *
   * @return boolean
   */
  public function beginTransaction() {
    if ($this->_inTransaction) {
      return false;
    }

    $this->_inTransaction = $this->_pdo->beginTransaction();
    return $this->_inTransaction;
  }

  /**
   * Closes the PDO connection by nulling it.  Since PDO instance is passed by
   * reference when instantiating.  This will nullify the original variable.  If
   * any other references to the PDO connection exist outside of clarinet they
   * need to be nullified before the connection will actually be closed.
   *
   * Calling this function will render any instantiate persisters useless.
   */
  public function close() {
    $this->_pdo = null;
  }

  /**
   * Passthrough for the commit() method.
   *
   * @return boolean
   */
  public function commit() {
    if (!$this->_inTransaction) {
      return false;
    }

    $this->_inTransaction = false;
    return $this->_pdo->commit();
  }

  /**
   * Escape the given string using the encapsulated PDO object's quote(...)
   * method.
   *
   * @param string $toQuote
   */
  public function quote($toQuote) {
    return $this->_pdo->quote($toQuote);
  }

  /**
   * Passthrough for the lastInsertId() method.
   *
   * @param string $name The name of the sequence object from which the ID
   *   should be returned.
   * @return string Id of the las row that was inserted into the database or
   *   sequence if name is provided.
   */
  public function lastInsertId($name = null) {
    return $this->_pdo->lastInsertId($name);
  }

  /**
   * Passthrough to the PDO object's prepare method.
   *
   * @param string $statment The SQL statement to prepare.
   * @param array $driverOpts Optional. Contains one or more key => value
   *   pairs to set attribute values for the PDOStatement that this method
   *   returns.
   */
  public function prepare($statement, $driverOpts = Array()) {
    return $this->_pdo->prepare($statement, $driverOpts);
  }

  /**
   * Passthrough for the rollback() method.
   *
   * @return boolean
   */
  public function rollback() {
    if (!$this->_inTransaction) {
      return false;
    }

    $this->_inTransaction = false;
    return $this->_pdo->rollback();
  }
}
