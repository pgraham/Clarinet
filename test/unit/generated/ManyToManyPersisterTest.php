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
namespace zpt\orm;

use \zpt\opal\CompanionLoader;
use \zpt\orm\test\mock\ManyToManyEntity;
use \zpt\orm\test\mock\SimpleEntity;
use \zpt\orm\test\Db;
use \zpt\orm\test\Generator;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../../setup.php';

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

  /* The object under test */
  private $persister;

  /**
   * Prepares a clean database, connects to it and instantiates the persister
   * that is the object under test.
   */
  protected function setUp() {
      Db::setUp();

      // Instantiate a generated persister to test
      $modelName = 'zpt\orm\test\mock\ManyToManyEntity';

      // Instantiate a generated persister to test
      $loader = new CompanionLoader();
      $this->persister = $loader->get('zpt\dyn\orm\persister', $modelName);
  }

  /**
   * Delete the sqlite database file that was used durring the tests and closes
   * any connections to it.  This involved cleaning up a bunch of caches,
   * notably in the ActorFactory.
   */
  protected function tearDown() {
    $this->persister = null;
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

    $id1 = $this->persister->create($lhs1);
    $this->assertNotNull($id1);
    $manyIds = Array();

    foreach ($many AS $e) {
      $this->assertNotNull($e->getId());
      $manyIds[$e->getId()] = $e->getId();
    }
    $this->assertEquals(10, count($manyIds));

    $id2 = $this->persister->create($lhs2);
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

    $id1 = $this->persister->create($lhs1);
    $id2 = $this->persister->create($lhs2);

    $this->persister->clearCache(array($id1, $id2));

    $retrieved1 = $this->persister->getById($id1);
    $this->assertNotNull($retrieved1);
    $this->assertInstanceOf('zpt\orm\test\mock\ManyToManyEntity', $retrieved1);

    $retrieved2 = $this->persister->getById($id2);
    $this->assertNotNull($retrieved2);
    $this->assertInstanceOf('zpt\orm\test\mock\ManyToManyEntity', $retrieved2);

    $this->assertFalse($retrieved1 === $retrieved2);

    $many1 = $retrieved1->getMany();
    $this->assertInternalType('array', $many1);
    $this->assertEquals(10, count($many1));
    foreach ($many1 AS $e) {
      $this->assertNotNull($e);
      $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $e);
      
      $e->setName('New' . $e->getName());
      $e->setValue('New' . $e->getValue());
    }

    $many2 = $retrieved2->getMany();
    $this->assertInternalType('array', $many2);
    $this->assertEquals(10, count($many2));
    foreach ($many2 AS $e) {
      $this->assertNotnull($e);
      $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $e);
      $this->assertRegExp('/^NewEntity\d+$/', $e->getName());
      $this->assertRegExp('/^NewEntity\d+Value$/', $e->getValue());
    }
  }

  /**
   * Tests that updating works as expected.
   */
  public function testUpdate() {
    $companionLoader = new CompanionLoader();
    $persister1 = $companionLoader->get(
      'zpt\dyn\orm\persister',
      'zpt\orm\test\mock\ManyToManyEntity',
      false /* No cache */
    );
    $persister2 = $companionLoader->get(
      'zpt\dyn\orm\persister',
      'zpt\orm\test\mock\ManyToManyEntity',
      false /* No cache */
    );

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

    $id1 = $persister1->create($lhs1);
    $this->assertEquals($id1, $lhs1->getId());
    $id2 = $persister1->create($lhs2);

    $newMany = Array();
    for ($i = 1; $i <= 10; $i++) {
      $e = new SimpleEntity();
      $e->setName("NewEntity$i");
      $e->setValue("NewEntity{$i}Value");
      $newMany[] = $e;
    }
    $lhs1->setMany($newMany);
    $persister1->update($lhs1);

    $retrieved1 = $persister2->getById($id1);
    $this->assertNotNull($retrieved1);
    $this->assertInstanceOf('zpt\orm\test\mock\ManyToManyEntity', $retrieved1);

    $retrieved2 = $persister2->getById($id2);
    $this->assertNotNull($retrieved2);
    $this->assertInstanceOf('zpt\orm\test\mock\ManyToManyEntity', $retrieved2);

    $many1 = $retrieved1->getMany();
    $this->assertInternalType('array', $many1);
    $this->assertEquals(10, count($many1));
    foreach ($many1 AS $e) {
      $this->assertNotNull($e);
      $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $e);
      $this->assertRegExp('/^NewEntity\d+$/', $e->getName());
      $this->assertRegExp('/^NewEntity\d+Value$/', $e->getValue());
    }

    $many2 = $retrieved2->getMany();
    $this->assertInternalType('array', $many2);
    $this->assertEquals(10, count($many2));
    foreach ($many2 AS $e) {
      $this->assertNotnull($e);
      $this->assertInstanceOf('zpt\orm\test\mock\SimpleEntity', $e);
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

    $id = $this->persister->create($lhs1);
    $this->persister->delete($lhs1);

    $this->assertNull($this->persister->getById($id));

    $pdo = PdoWrapper::get();
    $stmt = $pdo->prepare('SELECT COUNT(*)
      FROM many_to_many_entity_simple_entity_link
      WHERE many_to_many_entity_id = :id');
    $stmt->execute(Array(':id' => $id));

    $this->assertEquals(0, (int) $stmt->fetchColumn());
  }
}
