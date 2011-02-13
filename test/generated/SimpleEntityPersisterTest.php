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
 * @package clarinet/test/generated
 */
namespace clarinet\test\generated;

use \PHPUnit_Framework_TestCase as TestCase;
/**
 * This class tests a generated persister for a simple entity that contains only
 * scalar value columns.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/generated
 */
class SimpleEntityPersisterTest extends TestCase {

  /**
   * Suite wide setUp, ensure that all Mock classes have had their actor's
   * generated.
   */
  public static function setUpBeforeClass() {
    Generator::generate();
  }

  private $_pdo;
  private $_persister;

  protected function setUp() {
    // Database setUp, creates a clean database and returns a connection to it
    $this->_pdo = Db::setUp();

    // Instantiate a persister
    $modelName = 'clarinet\test\mock\SimpleEntity';
    $actorName = str_replace('\\' ,'_', $modelName);
    $className = "clarinet\\persister\\$actorName";

    // This should use the factory, need to determine a way to inject the PDO
    // connection Make sure PDO instance references are passed by reference so
    // that it can be easily nulled
    $this->_persister = new $className($this->_pdo);
  }

  protected function tearDown() {
    // Close the database connection
    $this->_persister = null;
    Db::tearDown($this->_pdo);
  }
}
