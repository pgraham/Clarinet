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

use \clarinet\test\mock\SimpleEntity;
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

  private $_persister;

  protected function setUp() {
    // Database setUp, creates a clean database and returns a connection to it
    Db::setUp();

    // Instantiate a persister
    $modelName = 'clarinet\test\mock\SimpleEntity';
    $actorName = str_replace('\\' ,'_', $modelName);
    $className = "clarinet\\persister\\$actorName";

    // This should use the factory, need to determine a way to inject the PDO
    // connection Make sure PDO instance references are passed by reference so
    // that it can be easily nulled
    $this->_persister = new $className();
  }

  protected function tearDown() {
    // Close the database connection
    $this->_persister = null;
    Db::tearDown();
  }

  public function testCreate() {
    $entity = new SimpleEntity();
    $entity->setName('Entity');
    $entity->setValue('EntityValue');

    $id = $this->_persister->create($entity);
    $this->assertNotNull($id);

    $retrieved = $this->_persister->getById($id);

    $this->assertEquals('Entity', $retrieved->getName());
    $this->assertEquals('EntityValue', $retrieved->getValue());

    // Make sure that the returned entity is the same instance as was originally
    // used to create
    $retrieved->setValue('NewEntityValue');
    $this->assertEquals('NewEntityValue', $entity->getValue());
  }
}
