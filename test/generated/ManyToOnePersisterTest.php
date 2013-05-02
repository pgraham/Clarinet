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

use \zpt\opal\CompanionLoader;
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

  /* Companion Loader */
  private $loader;

  /* The object under test */
  private $persister;

  /**
   * Prepare a clean database and the object under test.
   */
  protected function setUp() {
    Db::setUp();

    // Instantiate a generated persister to test
    $this->loader = new CompanionLoader();
    $this->persister = $this->loader->get(
      'zpt\dyn\orm\persister',
      'zpt\orm\test\mock\ManyToOneEntity'
    );
  }

  /**
   * Clean up the database and nullify the persister.  Database cleanup involves
   * closing this connection, cleaning any caches that reference the connection
   * and removing the sqlite database file that was used for the test.
   */
  protected function tearDown() {
    $this->persister = null;
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

    $id = $this->persister->create($manyToOne);
    $this->assertNotNull($id);
    $this->assertEquals($id, $manyToOne->getId());

    $retrieved = $this->persister->getById($id);
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

    $manyId = $this->persister->create($many);

    $this->persister->clearCache($manyId);

    $retrieved = $this->persister->getById($manyId);
    $this->assertEquals('ManyEntity', $retrieved->getName());
    
    $retrievedOne = $retrieved->getOne();
    $this->assertNotNull($retrievedOne);
    $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $retrievedOne);
    $this->assertEquals($one->getId(), $retrievedOne->getId());
    $this->assertTrue($one === $retrievedOne);
  }

  public function testUpdate() {
    $one = new SimpleEntity();
    $one->setName('Entity');
    $one->setValue('EntityValue');

    $many = new ManyToOneEntity();
    $many->setName('Many');
    $many->setOne($one);

    $manyId = $this->persister->create($many);

    $newOne = new SimpleEntity();
    $newOne->setName('NewEntity');
    $newOne->setValue('NewValue');
    $many->setOne($newOne);
    $this->persister->update($many);

    $this->assertNotNull($newOne->getId());

    $retrieved = $this->persister->getById($manyId);
    $retrievedOne = $retrieved->getOne();
    $this->assertNotNull($retrievedOne);
    $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $retrievedOne);
    $this->assertNotNull($retrievedOne->getId());
    $this->assertEquals($newOne->getId(), $retrievedOne->getId());

    // Retrieve with a new persister to avoid any cached models
    $this->persister->clearCache($manyId);

    $retrieved = $this->persister->getById($manyId);
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
    $manyId = $this->persister->create($many);
    $this->assertNotNull($manyId);

    $this->persister->delete($many);
    $retrieved = $this->persister->getById($manyId);
    $this->assertNull($retrieved);

    $this->persister->clearCache($manyId);

    $retrieved = $this->persister->getById($manyId);
    $this->assertNull($retrieved);
  }
}
