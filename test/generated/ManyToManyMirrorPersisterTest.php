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

use \clarinet\test\mock\ManyToManyMirrorLhsEntity;
use \clarinet\test\mock\ManyToManyMirrorRhsEntity;
use \clarinet\ActorFactory;

use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../test-common.php';

/**
 * This class tests a generated persister for a pair of entities that define a
 * mirrored many-to-many relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/generated
 */
class ManyToManyMirrorPersisterTest extends TestCase {

  /**
   * Suite wide setUp, ensure all mock classes have had their actors generated.
   */
  public static function setUpBeforeClass() {
    Generator::generate();
   // \clarinet\Autoloader::$genBasePath = __DIR__ . '/../mock/gen/clarinet';
  }

  /* The object under test */
  private $_persister;

  /**
   * Prepares a clean database, connects to it an instantiates the persister
   * that is the object under test.
   */
  protected function setUp() {
    Db::setUp();

    // Instantiate a generated persister to test
    $modelName = 'clarinet\test\mock\ManyToManyMirrorLhsEntity';
    $this->_persister = ActorFactory::getActor('persister', $modelName);
  }

  /**
   * Delete the sqlite database file that was used durring the tests and closes
   * any connections to it.  This involves cleaning up a bunch of caches,
   * notably in the ActorFactory.
   */
  protected function tearDown() {
    $this->_persister = null;
    Db::tearDown();
  }

  /**
   * Test creating.
   */
  public function testCreate() {
    $lhs = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToManyMirrorLhsEntity();
      $e->setName("Lhs$i");
      $lhs[] = $e;
    }

    $rhs = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToManyMirrorRhsEntity();
      $e->setName("Rhs$i");
      $rhs[] = $e;
    }

    foreach ($lhs AS $e) {
      $e->setMany($rhs);
    }
    foreach ($rhs AS $e) {
      $e->setMany($lhs);
    }

    $lhsIds = Array();
    foreach ($lhs AS $e) {
      $id = $this->_persister->create($e);
      $this->assertNotNull($id);
      $this->assertEquals($id, $e->getId());
      $lhsIds[] = $id;
    }

    $rhsIds = Array();
    foreach ($rhs AS $e) {
      $this->assertNotNull($e->getId());
      $rhsIds[] = $e->getId();
    }

    $createdLhs = $this->_persister->retrieve();
    $this->assertEquals(10, count($createdLhs));
  }

  /**
   * Test retrieving.
   */
  public function testRetrieve() {
    $lhs = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToManyMirrorLhsEntity();
      $e->setName("Lhs$i");
      $lhs[] = $e;
    }

    $rhs = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToManyMirrorRhsEntity();
      $e->setName("Rhs$i");
      $rhs[] = $e;
    }

    foreach ($lhs AS $e) {
      $e->setMany($rhs);
    }
    foreach ($rhs AS $e) {
      $e->setMany($lhs);
    }

    $lhsIds = Array();
    foreach ($lhs AS $e) {
      $id = $this->_persister->create($e);
      $this->assertNotNull($id);
      $this->assertEquals($id, $e->getId());
      $lhsIds[] = $id;
    }

    $rhsIds = Array();
    foreach ($rhs AS $e) {
      $this->assertNotNull($e->getId());
      $rhsIds[] = $e->getId();
    }

    $this->_persister->clearCache();

    foreach ($lhs AS $lhsE) {
      $retrieved = $this->_persister->getById($lhsE->getId());
      $this->assertNotNull($retrieved);
      $this->assertNotNull($retrieved->getId());
      $this->assertEquals($lhsE->getId(), $retrieved->getId());

      $retrievedRhs = $retrieved->getMany();
      $this->assertNotNull($retrievedRhs);
      $this->assertInternalType('array', $retrievedRhs);
      $this->assertEquals(10, count($retrievedRhs));

      foreach ($retrievedRhs AS $rhsE) {
        $this->assertContains($rhsE->getId(), $rhsIds);
      }
    }
  }

  /**
   * Test updating.
   */
  public function testUpdate() {
    $lhs = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToManyMirrorLhsEntity();
      $e->setName("Lhs$i");
      $lhs[] = $e;
    }

    $rhs = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToManyMirrorRhsEntity();
      $e->setName("Rhs$i");
      $rhs[] = $e;
    }

    foreach ($lhs AS $e) {
      $e->setMany($rhs);
    }
    foreach ($rhs AS $e) {
      $e->setMany($lhs);
    }

    $lhsIds = Array();
    foreach ($lhs AS $e) {
      $id = $this->_persister->create($e);
      $this->assertNotNull($id);
      $this->assertEquals($id, $e->getId());
      $lhsIds[] = $id;
    }

    $newRhs = Array();
    for ($i = 1; $i <= 5; $i++) {
      $e = new ManyToManyMirrorRhsEntity();
      $e->setName("NewRhs$i");
      $e->setMany($lhs);
      $newRhs[] = $e;
    }

    foreach ($lhs AS $e) {
      $e->setMany($newRhs);
      $updated = $this->_persister->update($e);
      $this->assertEquals(1, $updated);
    }

    $newRhsIds = Array();
    foreach ($newRhs AS $e) {
      $newRhsIds[] = $e->getId();
    }

    $this->_persister->clearCache();
    foreach ($lhs AS $lhsE) {
      $retrieved = $this->_persister->getById($lhsE->getId());
      $this->assertNotNull($retrieved);
      $this->assertNotNull($retrieved->getId());
      $this->assertEquals($lhsE->getId(), $retrieved->getId());

      $retrievedRhs = $retrieved->getMany();
      $this->assertNotNull($retrievedRhs);
      $this->assertInternalType('array', $retrievedRhs);
      $this->assertEquals(5, count($retrievedRhs));

      foreach ($retrievedRhs AS $rhsE) {
        $this->assertContains($rhsE->getId(), $newRhsIds);
      }
    }
  }

  /**
   * Test deleting.
   */
  public function testDelete() {
    $lhs = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToManyMirrorLhsEntity();
      $e->setName("Lhs$i");
      $lhs[] = $e;
    }

    $rhs = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new ManyToManyMirrorRhsEntity();
      $e->setName("Rhs$i");
      $rhs[] = $e;
    }

    foreach ($lhs AS $e) {
      $e->setMany($rhs);
    }
    foreach ($rhs AS $e) {
      $e->setMany($lhs);
    }

    $lhsIds = Array();
    foreach ($lhs AS $e) {
      $id = $this->_persister->create($e);
      $this->assertNotNull($id);
      $this->assertEquals($id, $e->getId());
      $lhsIds[] = $id;
    }

    $rhsIds = Array();
    foreach ($rhs AS $e) {
      $this->assertNotNull($e->getId());
      $rhsIds[] = $e->getId();
    }

    $deletedId = $lhs[0]->getId();
    $this->_persister->delete($lhs[0]);

    $this->_persister->clearCache();
    $lhs = Array();
    $rhs = Array();

    $lhs = $this->_persister->retrieve();
    $this->assertInternalType('array', $lhs);
    $this->assertEquals(9, count($lhs));

    foreach ($lhs AS $lhsE) {
      $this->assertNotEquals($deletedId, $lhsE->getId());
      $this->assertContains($lhsE->getId(), $lhsIds);
      
      $rhs = $lhsE->getMany();
      $this->assertInternalType('array', $rhs);
      $this->assertEquals(10, count($rhs));

      foreach ($rhs AS $rhsE) {
        $this->assertNotNull($rhsE);
        $this->assertNotNull($rhsE->getId());
        $this->assertContains($rhsE->getId(), $rhsIds);
      }
    }
  }
}
