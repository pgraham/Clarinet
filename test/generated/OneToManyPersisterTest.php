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

use \clarinet\Criteria;
use \clarinet\test\mock\OneToManyEntity;
use \clarinet\test\mock\OneToManyRhs;

use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../test-common.php';

/**
 * This class tests a generated persister for an entity that contains a
 * OneToMany relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/generated
 */
class OneToManyPersisterTest extends TestCase {

  /**
   * Suite wide setUp, ensures all Mock classes have had their actors generated.
   */
  public static function setUpBeforeClass() {
    Generator::generate();
  }

  /* The object under test */
  private $_persister;

  /**
   * Prepares a clean database, connects to it and instantiates the persister
   * that is the object under test.
   */
  protected function setUp() {
    Db::setUp();

    // Instantiate a generated persister to test
    $modelName = 'clarinet\test\mock\OneToManyEntity';
    $actorName = str_replace('\\', '_', $modelName);
    $className = "clarinet\\persister\\" . $actorName;
    $this->_persister = new $className();
  }

  /**
   * Deletes the sqlite database file that was used durring the tests and closes
   * any connections to it.  This involved cleaning up a bunch of caches,
   * notably in the ActorFactory.
   */
  protected function tearDown() {
    $this->_persister = null;
    Db::tearDown();
  }

  /**
   * Test creating an entity with many related entities, all of which are new.
   */
  public function testCreateWithNewRelated() {
    $one = new OneToManyEntity();
    $one->setName('One');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $o = new OneToManyRhs();
      $o->setName("Entity$i");
      $many[] = $o;
    }
    $one->setMany($many);
    $id = $this->_persister->create($one);
    $this->assertNotNull($id);
    $this->assertEquals($id, $one->getId());

    foreach ($one->getMany() AS $o) {
      $this->assertNotNull($o);
      $this->assertInstanceOf('clarinet\test\mock\OneToManyRhs', $o);
      $this->assertNotNull($o->getId());
    }
  }

  /**
   * Test create an entity with many related entities, all of which have already
   * been created but need updating.
   */
  public function testCreateWithExistingRelated() {
    $originalOne = new OneToManyEntity();
    $originalOne->setName('OriginalOne');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new OneToManyRhs();
      $e->setName("Entity$i");
      $many[] = $e;
    }
    $originalOne->setMany($many);
    $this->_persister->create($originalOne);

    foreach ($many AS $e) {
      $e->setName("New{$e->getName()}");
    }

    $one = new OneToManyEntity();
    $one->setName('One');
    $one->setMany($many);

    $id = $this->_persister->create($one);

    $this->assertNotNull($id);
    $this->assertEquals($id, $one->getId());

    // Retrieve the entities using their persister and ensure that the returned
    // object's have updated values
    $modelName = 'clarinet\test\mock\OneToManyRhs';
    $actorName = str_replace('\\', '_', $modelName);
    $className = "clarinet\\persister\\" . $actorName;
    $manyPersister = new $className();
    
    $c = new Criteria();
    $c->addEquals('one_to_many_entity_id', $id);
    $retrieved = $manyPersister->retrieve($c);
    foreach ($retrieved AS $e) {
      $this->assertRegExp('/^NewEntity\d+$/', $e->getName());
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
    $id = $this->_persister->create($one);

    // Use a clean persister to retrieve so that the entity isn't retrieved from
    // cache
    $className = get_class($this->_persister);
    $persister = new $className();

    $retrieved = $persister->getById($id);
    $this->assertNotNull($retrieved);
    $this->assertInstanceOf('clarinet\test\mock\OneToManyEntity', $retrieved);

    $many = $retrieved->getMany();
    $this->assertInternalType('array', $many);
    $this->assertEquals(10, count($many));

    foreach ($many AS $e) {
      $this->assertNotNull($e->getId());
      $this->assertEquals($id, $e->getOneToManyEntityId());
      $this->assertRegExp('/^Entity\d+$/', $e->getName());
    }
  }
}
