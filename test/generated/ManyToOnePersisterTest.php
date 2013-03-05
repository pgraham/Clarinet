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
use \zpt\orm\test\mock\ManyToOneEntity;
use \zpt\orm\test\Db;
use \zpt\orm\test\Generator;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../test-common.php';

/**
 * This class tests a generated persister for an entity that contains a
 * ManyToOne relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/generated
 */
class ManyToOnePersisterTest extends TestCase {
  
  /*
   * Suite wide setUp, ensures all Mock classes have had their actor's
   * generated.
   */
  public static function setUpBeforeClass() {
    Generator::generate();
  }

  /* The object under test */
  private $_persister;

  /**
   * Prepare a clean database and the object under test.
   */
  protected function setUp() {
    Db::setUp();

    // Instantiate a generated persister to test
    $modelName = 'zpt\orm\test\mock\ManyToOneEntity';
    $actorName = str_replace('\\', '_', $modelName);
    $className = "zpt\\dyn\\orm\\persister\\" . $actorName;
    $this->_persister = new $className();
  }

  /**
   * Clean up the database and nullify the persister.  Database cleanup involves
   * closing this connection, cleaning any caches that reference the connection
   * and removing the sqlite database file that was used for the test.
   */
  protected function tearDown() {
    $this->_persister = null;
    Db::tearDown();
  }

  /**
   * Tests that ManyToOne relationships are handled correctly by the persisters
   * create method.
   */
  public function testCreate() {
    $one = new SimpleEntity();
    $one->setName('entity1');
    $one->setValue('entity1value');

    $manyToOne = new ManyToOneEntity();
    $manyToOne->setName('manyToOneEntity1');
    $manyToOne->setOne($one);

    $id = $this->_persister->create($manyToOne);
    $this->assertNotNull($id);
    $this->assertEquals($id, $manyToOne->getId());

    $retrieved = $this->_persister->getById($id);
    $this->assertNotNull($retrieved);
    $this->assertInstanceOf('zpt\orm\test\mock\ManyToOneEntity', $retrieved);
    $this->assertEquals('manyToOneEntity1', $retrieved->getName());

    $retrievedOne = $retrieved->getOne();
    $this->assertNotNull($retrievedOne);
    $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $retrievedOne);
    $this->assertNotNull($retrievedOne->getId());
    $this->assertEquals($one->getId(), $retrievedOne->getId());
    $this->assertEquals('entity1', $retrievedOne->getName());
    $this->assertEquals('entity1value', $retrievedOne->getValue());
  }

  public function testRetrieve() {
    $one = new SimpleEntity();
    $one->setName('SimpleEntity');
    $one->setValue('SimpleEntityValue');

    $many = new ManyToOneEntity();
    $many->setName('ManyEntity');
    $many->setOne($one);

    $manyId = $this->_persister->create($many);

    // Use a new persister to avoid the cache
    $className = get_class($this->_persister);
    $persister = new $className();

    $retrieved = $persister->getById($manyId);
    $this->assertEquals('ManyEntity', $retrieved->getName());
    
    $retrievedOne = $retrieved->getOne();
    $this->assertNotNull($retrievedOne);
    $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $retrievedOne);
    $this->assertEquals($one->getId(), $retrievedOne->getId());
  }

  public function testUpdate() {
    $one = new SimpleEntity();
    $one->setName('Entity');
    $one->setValue('EntityValue');

    $many = new ManyToOneEntity();
    $many->setName('Many');
    $many->setOne($one);

    $manyId = $this->_persister->create($many);

    $newOne = new SimpleEntity();
    $newOne->setName('NewEntity');
    $newOne->setValue('NewValue');
    $many->setOne($newOne);
    $this->_persister->update($many);

    $this->assertNotNull($newOne->getId());

    $retrieved = $this->_persister->getById($manyId);
    $retrievedOne = $retrieved->getOne();
    $this->assertNotNull($retrievedOne);
    $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $retrievedOne);
    $this->assertNotNull($retrievedOne->getId());
    $this->assertEquals($newOne->getId(), $retrievedOne->getId());

    // Retrieve with a new persister to avoid any cached models
    $className = get_class($this->_persister);
    $persister = new $className();

    $retrieved = $persister->getById($manyId);
    $retrievedOne = $retrieved->getOne();
    $this->assertNotNull($retrievedOne);
    $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $retrievedOne);
    $this->assertNotNull($retrievedOne->getId());
    $this->assertEquals($newOne->getId(), $retrievedOne->getId());
  }

  public function testDelete() {
    $one = new SimpleEntity();
    $one->setName('Entity');
    $one->setValue('EntityValue');

    $many = new ManyToOneEntity();
    $many->setName('Many');
    $many->setOne($one);
    $manyId = $this->_persister->create($many);
    $this->assertNotNull($manyId);

    $this->_persister->delete($many);
    $retrieved = $this->_persister->getById($manyId);
    $this->assertNull($retrieved);

    // Use a new persister to ensure that the delete was actually done in the
    // database
    $className = get_class($this->_persister);
    $persister = new $className();

    $retrieved = $persister->getById($manyId);
    $this->assertNull($retrieved);
  }
}
