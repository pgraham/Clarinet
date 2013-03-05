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
use \zpt\orm\test\mock\SimpleEntity;
use \zpt\orm\test\Db;
use \zpt\orm\test\Generator;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../test-common.php';

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
    Db::setUp();

    // Instantiate a persister
    $modelName = 'zpt\orm\test\mock\SimpleEntity';
    $actorName = str_replace('\\' ,'_', $modelName);
    $className = "zpt\\dyn\\orm\\persister\\$actorName";
    $this->_persister = new $className();
  }

  protected function tearDown() {
    $this->_persister = null;
    Db::tearDown();
  }

  public function testCreate() {
    $entity = new SimpleEntity();
    $entity->setName('Entity');
    $entity->setValue('EntityValue');

    $id = $this->_persister->create($entity);
    $this->assertNotNull($id);
    $this->assertEquals($id, $entity->getId());

    $retrieved = $this->_persister->getById($id);
    $this->assertNotNull($retrieved);
    $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $retrieved);
    $this->assertEquals('Entity', $retrieved->getName());
    $this->assertEquals('EntityValue', $retrieved->getValue());

    // Make sure that the returned entity is the same instance as was originally
    // used to create
    $retrieved->setValue('NewEntityValue');
    $this->assertEquals('NewEntityValue', $entity->getValue());
  }

  public function testRetrieve() {
    $entity1 = new SimpleEntity();
    $entity1->setName('Entity1');
    $entity1->setValue('Entity1Value');
    $this->_persister->create($entity1);

    $entity2 = new SimpleEntity();
    $entity2->setName('Entity2');
    $entity2->setValue('Entity2Value');
    $this->_persister->create($entity2);

    $entities = $this->_persister->retrieve();
    $retrieved1 = null;
    $retrieved2 = null;
    foreach ($entities AS $entity) {
      if ($entity->getName() == 'Entity1') {
        $retrieved1 = $entity;
      } else if ($entity->getName() == 'Entity2') {
        $retrieved2 = $entity;
      }
    }
    $this->assertNotNull($retrieved1);
    $this->assertNotNull($retrieved2);
  }

  /**
   * Tests that entities retrieves and then modified, without updating, will
   * reflect those changes if retrieved a second time.
   */
  public function testRetrieveEditRetrieve() {
    $entity = new SimpleEntity();
    $entity->setName('Entity');
    $entity->setValue('EntityValue');
    $id = $this->_persister->create($entity);
    $this->assertNotNull($id);

    $retrieved = $this->_persister->getById($id);
    $retrieved->setValue('NewEntityValue');

    $retrievedAgain = $this->_persister->getById($id);
    $this->assertEquals('NewEntityValue', $retrievedAgain->getValue());
  }

  /**
   * Test retrieving a non-cached object.
   */
  public function testRetrieveNoCache() {
    $entity = new SimpleEntity();
    $entity->setName('Entity');
    $entity->setValue('EntityValue');
    $id = $this->_persister->create($entity);
    $this->assertNotNull($id);

    // Use a new persister to ensure that there are no cached models
    $className = get_class($this->_persister);
    $persister = new $className();

    $retrieved = $persister->getById($id);
    $this->assertEquals('Entity', $retrieved->getName());
    $this->assertEquals('EntityValue', $retrieved->getValue());
  }

  /**
   * Tests that the update method properly saves changes in the database.
   */
  public function testUpdate() {
    $entity = new SimpleEntity();
    $entity->setName('Entity');
    $entity->setValue('EntityValue');
    $id = $this->_persister->create($entity);
    $this->assertNotNull($id);

    $entity->setValue('NewEntityValue');
    $this->_persister->update($entity);

    // Use a new persister to ensure that there are no cached models
    $className = get_class($this->_persister);
    $persister = new $className();

    $retrieved = $persister->getById($id);
    $this->assertEquals('Entity', $retrieved->getName());
    $this->assertEquals('NewEntityValue', $retrieved->getValue());
  }

  /**
   * Tests that the delete method properly deletes an entity from the database
   * and the cache.
   */
  public function testDelete() {
    $entity = new SimpleEntity();
    $entity->setName('Entity');
    $entity->setValue('EntityValue');
    $id = $this->_persister->create($entity);
    $this->assertNotNull($id);

    $this->_persister->delete($entity);
    $retrieved = $this->_persister->getById($id);
    $this->assertNull($retrieved);

    // Use a new persister to ensure that the delete was actually done in the
    // database
    $className = get_class($this->_persister);
    $persister = new $className();

    $retrieved = $persister->getById($id);
    $this->assertNull($retrieved);
  }
}
