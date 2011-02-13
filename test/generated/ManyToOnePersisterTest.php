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
use \clarinet\test\mock\ManyToOneEntity;

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
  
  /*
   * Suite wide setUp, ensures all Mock classes have had their actor's
   * generated.
   */
  public static function setUpBeforeClass() {
    Generator::generate();
  }

  private $_pdo;
  private $_persister;

  protected function setUp() {
    // Connect to the database
    $this->_pdo = Db::setUp();

    // Instantiate a generated persister to test
    $modelName = 'clarinet\test\mock\ManyToOneEntity';
    $actorName = str_replace('\\', '_', $modelName);
    $className = "clarinet\\persister\\" . $actorName;

    // This should use the factory, need to determine a way to inject the PDO
    // connection
    $this->_persister = new $className($this->_pdo);
  }

  protected function tearDown() {
    // Close the database connection
    $this->_persister = null;
    Db::tearDown($this->_pdo);
  }

  public function testCreate() {
    $one = new SimpleEntity();
    $one->setName('entity1');
    $one->setValue('entity1value');

    $manyToOne = new ManyToOneEntity();
    $manyToOne->setName('manyToOneEntity1');
    $manyToOne->setOne($one);

    $this->_persister->create($manyToOne);

    $c = new Criteria();
    $c->addEquals('name', 'manyToOneEntity1');
    $entities = $this->_persister->retrieve($c);
    $msg = print_r($entities, true);

    $this->assertInternalType('array', $entities, $msg);
    $this->assertEquals(1, count($entities), $msg);

    $entity = $entities[0];
    $msg = print_r($entity, true);
    $this->assertInstanceOf('clarinet\test\mock\ManyToOneEntity', $entity,
      $msg);

    $retrievedOne = $entity->getOne();
    $msg = print_r($retrievedOne, true);
    $this->assertInstanceOf('clarinet\test\mock\SimpleEntity', $retrievedOne,
      $msg);
    $this->assertEquals('entity1', $retrievedOne->getName(), $msg);
    $this->assertEquals('entity1value', $retrievedOne->getValue(), $msg);
  }
}
