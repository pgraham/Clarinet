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

require_once __DIR__ . '/../test-common.php';

/**
 * This class tests a generated persister for an entity that contains a
 * ManyToOne relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/generated
 */
class ManyToOneTest extends TestCase {

  private $_pdo;

  protected function setUp() {
    // Connect to the database
    $this->_pdo = Db::setUp();
  }

  protected function tearDown() {
    // Close the database connection
    Db::tearDown($this->_pdo);
  }

  public function testCreate() {
    $stmt = $this->_pdo->prepare("INSERT INTO simple_entity (name, value)
      VALUES (:name, :value)");
    $stmt->execute(Array(':name' => 'myName', ':value' => 'myValue'));

    $stmt = $this->_pdo->prepare("SELECT * FROM simple_entity");
    $stmt->execute();
    foreach ($stmt AS $row) {
      print_r($row);
    }
  }

  public function testCreateAgain() {
    $stmt = $this->_pdo->prepare("INSERT INTO simple_entity (name, value)
      VALUES (:name, :value)");
    $stmt->execute(Array(':name' => 'myName', ':value' => 'myValue'));

    $stmt = $this->_pdo->prepare("SELECT * FROM simple_entity");
    $stmt->execute();
    foreach ($stmt AS $row) {
      print_r($row);
    }
  }
}
