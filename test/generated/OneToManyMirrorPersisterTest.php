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

use \clarinet\test\mock\ManyToOneMirrorEntity;
use \clarinet\test\mock\OneToManyMirrorEntity;
use \clarinet\ActorFactory;
use \clarinet\Criteria;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../test-common.php';

/**
 * This class tests the generated persisters for a pair of  entities that
 * contain a mirrored one-to-many relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/generated
 */
class OneToManyMirrorPersisterTest extends TestCase {

  /**
   * Suite wide setUp, ensure all Mock classes have had their actors generated.
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
    $modelName = 'clarinet\test\mock\OneToManyMirrorEntity';
    $actorName = str_replace('\\', '_', $modelName);
    $className = "clarinet\\persister\\" . $actorName;
    $this->_persister = new $className();
  }

  /**
   * Delete the sqlite database file that was used durring the tests and closes
   * any connections to it.  This involved cleaning up a bunch of caches,
   * notable in the ActorFactory.
   */
  protected function tearDown() {
    $this->_persister = null;
    Db::tearDown();
  }

  /**
   * Test creating an entity with many related entities, all of which are new.
   */
  public function testCreate() {
    $one = new OneToManyMirrorEntity();
    $one->setName('One');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToOneMirrorEntity();
      $e->setName("Many$i");
      $many[] = $e;
    }
    $one->setMany($many);

    $id = $this->_persister->create($one);
    $this->assertNotNull($id);
    
    foreach ($many AS $e) {
      $this->assertNotNull($e->getId());
    }

    // Ensure that only 10 rhs entities have been created
    $persister = ActorFactory::getActor('persister',
      'clarinet\test\mock\ManyToOneMirrorEntity');
    $rhs = $persister->retrieve();
    $this->assertEquals(10, count($rhs));

    foreach ($rhs AS $e) {
      $this->assertEquals($id, $e->getOne()->getId());
    }
  }

  /**
   * Test updating an entity with new related entities.
   */
  public function testUpdateWithNew() {
    $one = new OneToManyMirrorEntity();
    $one->setName('One');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToOneMirrorEntity();
      $e->setName("Many$i");
      $many[] = $e;
    }
    $one->setMany($many);

    $id = $this->_persister->create($one);
    $this->assertNotNull($e->getId());

    $oldIds = Array();
    foreach ($many AS $e) {
      $oldIds[] = $e->getId();
    }

    $newMany = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToOneMirrorEntity();
      $e->setName("NewMany$i");
      $newMany[] = $e;
    }
    $one->setMany($newMany);
    $this->_persister->update($one);

    foreach ($newMany AS $e) {
      $this->assertNotNull($e->getId());
    }
  }

  /**
   * Test retrieving a one-to-many mirrored relationship.
   */
  public function testRetrieve() {
    $one = new OneToManyMirrorEntity();
    $one->setName('One');

    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToOneMirrorEntity();
      $e->setName("Many$i");
      $many[] = $e;
    }
    $one->setMany($many);

    $id = $this->_persister->create($one);
    $this->assertNotNull($id);

    $ids = Array();
    foreach ($many AS $e) {
      $this->assertNotNull($e->getId());
      $ids[] = $e->getId();
    }

    $className = get_class($this->_persister);
    $persister = new $className();
    $retrieved = $persister->getById($id);
    $this->assertNotNull($retrieved);
    
    $retrievedMany = $retrieved->getMany();
    $this->assertNotNull($retrievedMany);
    $this->assertInternalType('array', $retrievedMany);
    $this->assertEquals(10, count($retrievedMany));

    foreach ($retrievedMany AS $e) {
      $this->assertNotNull($e);
      $this->assertNotNull($e->getId());
      $this->assertContains($e->getId(), $ids);
    }
  }

  /**
   * Tests deleting a one to many relationship.
   */
  public function testDelete() {
    $one = new OneToManyMirrorEntity();
    $one->setName('One');
    
    $many = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToOneMirrorEntity();
      $e->setName("Many$i");
      $many[] = $e;
    }
    $one->setMany($many);

    $id = $this->_persister->create($one);
    $this->assertNotNull($id);

    $this->_persister->delete($one);
    $this->assertNull($one->getId());
    $this->assertNull($this->_persister->getById($id));
    foreach ($many AS $e) {
      $this->assertNull($e->getId());
    }

    $className = get_class($this->_persister);
    $persister = new $className();
    $this->assertNull($persister->getById($id));

    $className = get_class($many[0]);
    $persister = ActorFactory::getActor('persister', $className);

    $c = new Criteria();
    $c->addEquals('one_to_many_mirror_id', $id);
    $retrieved = $persister->retrieve($c);
    $this->assertInternalType('array', $retrieved);
    $this->assertEmpty($retrieved);
  }
}
