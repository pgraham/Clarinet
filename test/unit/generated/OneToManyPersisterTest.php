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
namespace zpt\orm\companion;

use \zpt\opal\CompanionLoader;
use \zpt\orm\test\mock\OneToManyEntity;
use \zpt\orm\test\mock\OneToManyRhs;
use \zpt\orm\test\Generator;
use \zpt\orm\test\Db;
use \zpt\orm\Criteria;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../../setup.php';

/**
 * This class tests a generated persister for an entity that contains a
 * OneToMany relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class OneToManyPersisterTest extends TestCase {

  /**
   * Suite wide setUp, ensures all Mock classes have had their actors generated.
   */
  public static function setUpBeforeClass() {
    Generator::generate();
  }

  /* Companion loader */
  private $loader;

  /* The object under test */
  private $persister;

  /**
   * Prepares a clean database, connects to it and instantiates the persister
   * that is the object under test.
   */
  protected function setUp() {
    Db::setUp();

    // Instantiate a generated persister to test
    global $dynTarget;
    $this->loader = new CompanionLoader('persister', $dynTarget);
    $this->persister = $this->loader->get('zpt\orm\test\mock\OneToManyEntity');
  }

  /**
   * Deletes the sqlite database file that was used durring the tests and closes
   * any connections to it.  This involved cleaning up a bunch of caches,
   * notably in the ActorFactory.
   */
  protected function tearDown() {
    $this->persister = null;
    Db::tearDown();
  }

  /**
   * Test creating an entity with many related entities, all of which are new.
   */
  public function testCreate() {
    $one = new OneToManyEntity();
    $one->setName('One');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $o = new OneToManyRhs();
      $o->setName("Entity$i");
      $many[] = $o;
    }
    $one->setMany($many);
    $id = $this->persister->create($one);
    $this->assertNotNull($id);
    $this->assertEquals($id, $one->getId());

    foreach ($one->getMany() AS $o) {
      $this->assertNotNull($o);
      $this->assertInstanceOf('zpt\orm\test\mock\OneToManyRhs', $o);
      $this->assertNotNull($o->getId());
    }
  }

  /**
   * Test that retrieving an entity with a one-to-many relationship retrieves
   * its related entities as well.
   */
  public function testRetrieve() {
    $one = new OneToManyEntity();
    $one->setName('One');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new OneToManyRhs();
      $e->setName("Entity$i");
      $many[] = $e;
    }
    $one->setMany($many);
    $id = $this->persister->create($one);

    // Use a clean persister to retrieve so that the entity isn't retrieved from
    // cache
    $this->persister->clearCache($id);

    $retrieved = $this->persister->getById($id);
    $this->assertNotNull($retrieved);
    $this->assertInstanceOf('zpt\orm\test\mock\OneToManyEntity', $retrieved);

    $many = $retrieved->getMany();
    $this->assertInternalType('array', $many);
    $this->assertEquals(10, count($many));

    foreach ($many AS $e) {
      $this->assertNotNull($e->getId());
      $this->assertEquals($id, $e->getOneToManyEntityId());
      $this->assertRegExp('/^Entity\d+$/', $e->getName());
    }
  }

  /**
   * Test that updating a one-to-many relationship works as expected.
   */
  public function testUpdate() {
    $one = new OneToManyEntity();
    $one->setName('One');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new OneToManyRhs();
      $e->setName("Entity$i");
      $many[] = $e;
    }
    $one->setMany($many);
    $id = $this->persister->create($one);

    unset($many[9]);
    unset($many[8]);

    $one->setMany($many);
    $this->persister->update($one);

    // Retrieve with a new persister to ensure that the orphaned entities were
    // deleted
    $this->persister->clearCache($id);

    $retrieved = $this->persister->getById($id);
    $retrievedMany = $retrieved->getMany();
    $this->assertEquals(8, count($retrievedMany));

    for ($i = 9; $i <= 10; $i++) {
      $e = new OneToManyRhs();
      $e->setName("Entity$i");
      $many[] = $e;
    }
    $one->setMany($many);
    $this->persister->update($one);

    $this->persister->clearCache($id);
    $retrieved = $this->persister->getById($id);
    $retrievedMany = $retrieved->getMany();
    $this->assertEquals(10, count($retrievedMany));
  }

  /**
   * Test that deleting an entity with a one-to-many relationship works as
   * expected.
   */
  public function testDelete() {
    $one = new OneToManyEntity();
    $one->setName('One');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new OneToManyRhs();
      $e->setName("Entity$i");
      $many[] = $e;
    }
    $one->setMany($many);
    $id = $this->persister->create($one);
    $this->assertNotNull($id);

    $this->persister->delete($one);
    $retrieved = $this->persister->getById($id);
    $this->assertNull($retrieved);

    $rhsPersister = $this->loader->get('zpt\orm\test\mock\OneToManyRhs');

    $c = new Criteria();
    $c->addEquals('one_to_many_entity_id', $id);
    $retrieved = $rhsPersister->retrieve($c);
    $this->assertInternalType('array', $retrieved);
    $this->assertEquals(0, count($retrieved));
  }
}
