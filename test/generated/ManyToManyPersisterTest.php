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

use \clarinet\test\mock\ManyToManyEntity;
use \clarinet\test\mock\SimpleEntity;
use \clarinet\PdoWrapper;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../test-common.php';

/**
 * This class tests a generated persister for an entity that contains a
 * many-to-many relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/generated
 */
class ManyToManyPersisterTest extends TestCase {

  /**
   * Suite wide setUp, ensure all Mock classes have had their actors generated.
   */
  public static function setUpBeforeClass() {
    Generator::generate();
  }

  /* The object user test */
  private $_persister;

  /**
   * Prepares a clean database, connects to it and instantiates the persister
   * that is the object under test.
   */
  protected function setUp() {
    Db::setUp();

    // Instantiate a generated persister to test
    $modelName = 'clarinet\test\mock\ManyToManyEntity';
    $actorName = str_replace('\\', '_', $modelName);
    $className = "clarinet\\persister\\" . $actorName;
    $this->_persister = new $className();
  }

  /**
   * Delete the sqlite database file that was used durring the tests and closes
   * any connections to it.  This involved cleaning up a bunch of caches,
   * notably in the ActorFactory.
   */
  protected function tearDown() {
    $this->_persister = null;
    Db::tearDown();
  }

  /**
   * Test creating an entity with many related entites, all of which are new.
   */
  public function testCreate() {
    $lhs1 = new ManyToManyEntity();
    $lhs1->setName('Lhs1');

    $lhs2 = new ManyToManyEntity();
    $lhs2->setName('Lhs2');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new SimpleEntity();
      $e->setName("Entity$i");
      $e->setValue("Entity{$i}Value");
      $many[] = $e;
    }

    $lhs1->setMany($many);
    $lhs2->setMany($many);

    $id1 = $this->_persister->create($lhs1);
    $this->assertNotNull($id1);
    $manyIds = Array();

    foreach ($many AS $e) {
      $this->assertNotNull($e->getId());
      $manyIds[$e->getId()] = $e->getId();
    }
    $this->assertEquals(10, count($manyIds));

    $id2 = $this->_persister->create($lhs2);
    $this->assertNotNull($id2);
    $this->assertNotEquals($id1, $id2);

    foreach ($many AS $e) {
      $this->assertNotNull($e->getId());
      $this->assertArrayHasKey($e->getId(), $manyIds);
    }
  }

  /**
   * Tests that retrieving an entity works as expected.
   */
  public function testRetrieve() {
    // Use a second persister to create the mock entities to avoid hitting the
    // cache of the OUT
    $className = get_class($this->_persister);
    $persister = new $className();

    $lhs1 = new ManyToManyEntity();
    $lhs1->setName('Lhs1');

    $lhs2 = new ManyToManyEntity();
    $lhs2->setName('Lhs2');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new SimpleEntity();
      $e->setName("Entity$i");
      $e->setValue("Entity{$i}Value");
      $many[] = $e;
    }

    $lhs1->setMany($many);
    $lhs2->setMany($many);

    $id1 = $persister->create($lhs1);
    $id2 = $persister->create($lhs2);

    $retrieved1 = $this->_persister->getById($id1);
    $this->assertNotNull($retrieved1);
    $this->assertInstanceOf('clarinet\test\mock\ManyToManyEntity', $retrieved1);

    $retrieved2 = $this->_persister->getById($id2);
    $this->assertNotNull($retrieved2);
    $this->assertInstanceOf('clarinet\test\mock\ManyToManyEntity', $retrieved2);

    $many1 = $retrieved1->getMany();
    $this->assertInternalType('array', $many1);
    $this->assertEquals(10, count($many1));
    foreach ($many1 AS $e) {
      $this->assertNotNull($e);
      $this->assertInstanceOf('clarinet\test\mock\SimpleEntity', $e);
      
      $e->setName('New' . $e->getName());
      $e->setValue('New' . $e->getValue());
    }

    $many2 = $retrieved2->getMany();
    $this->assertInternalType('array', $many2);
    $this->assertEquals(10, count($many2));
    foreach ($many2 AS $e) {
      $this->assertNotnull($e);
      $this->assertInstanceOf('clarinet\test\mock\SimpleEntity', $e);
      $this->assertRegExp('/^NewEntity\d+$/', $e->getName());
      $this->assertRegExp('/^NewEntity\d+Value$/', $e->getValue());
    }
  }

  /**
   * Tests that updating works as expected.
   */
  public function testUpdate() {
    $lhs1 = new ManyToManyEntity();
    $lhs1->setName('Lhs1');

    $lhs2 = new ManyToManyEntity();
    $lhs2->setName('Lhs2');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new SimpleEntity();
      $e->setName("Entity$i");
      $e->setValue("Entity{$i}Value");
      $many[] = $e;
    }

    $lhs1->setMany($many);
    $lhs2->setMany($many);

    $id1 = $this->_persister->create($lhs1);
    $this->assertEquals($id1, $lhs1->getId());
    $id2 = $this->_persister->create($lhs2);

    $newMany = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new SimpleEntity();
      $e->setName("NewEntity$i");
      $e->setValue("NewEntity{$i}Value");
      $newMany[] = $e;
    }
    $lhs1->setMany($newMany);
    $this->_persister->update($lhs1);

    // Use a second persister to retrieve the entities to assert against in
    // order to avoid hitting the cache
    $className = get_class($this->_persister);
    $persister = new $className();

    $retrieved1 = $persister->getById($id1);
    $this->assertNotNull($retrieved1);
    $this->assertInstanceOf('clarinet\test\mock\ManyToManyEntity', $retrieved1);

    $retrieved2 = $persister->getById($id2);
    $this->assertNotNull($retrieved2);
    $this->assertInstanceOf('clarinet\test\mock\ManyToManyEntity', $retrieved2);

    $many1 = $retrieved1->getMany();
    $this->assertInternalType('array', $many1);
    $this->assertEquals(10, count($many1));
    foreach ($many1 AS $e) {
      $this->assertNotNull($e);
      $this->assertInstanceOf('clarinet\test\mock\SimpleEntity', $e);
      $this->assertRegExp('/^NewEntity\d+$/', $e->getName());
      $this->assertRegExp('/^NewEntity\d+Value$/', $e->getValue());
    }

    $many2 = $retrieved2->getMany();
    $this->assertInternalType('array', $many2);
    $this->assertEquals(10, count($many2));
    foreach ($many2 AS $e) {
      $this->assertNotnull($e);
      $this->assertInstanceOf('clarinet\test\mock\SimpleEntity', $e);
      $this->assertRegExp('/^Entity\d+$/', $e->getName());
      $this->assertRegExp('/^Entity\d+Value$/', $e->getValue());
    }
  }

  /**
   * Tests that deleting works as expected.
   */
  public function testDelete() {
    $lhs1 = new ManyToManyEntity();
    $lhs1->setName('Lhs1');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new SimpleEntity();
      $e->setName("Entity$i");
      $e->setValue("Entity{$i}Value");
      $many[] = $e;
    }
    $lhs1->setMany($many);

    $id = $this->_persister->create($lhs1);
    $this->_persister->delete($lhs1);

    $this->assertNull($this->_persister->getById($id));

    $pdo = PdoWrapper::get();
    $stmt = $pdo->prepare('SELECT COUNT(*)
      FROM many_to_many_entity_simple_entity_link
      WHERE many_to_many_entity_id = :id');
    $stmt->execute(Array(':id' => $id));

    $this->assertEquals(0, (int) $stmt->fetchColumn());
  }
}
