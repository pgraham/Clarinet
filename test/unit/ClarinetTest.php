<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.	The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\orm;

use \PHPUnit_Framework_TestCase as TestCase;

use \zpt\opal\CompanionLoader;
use \zpt\orm\companion\PersisterCompanionDirector;
use \zpt\orm\test\mock\SimpleEntity;
use \zpt\orm\test\Db;
use \zpt\orm\test\Generator;
use \PDO;

require_once __DIR__ . '/../setup.php';

/**
 * This class tests the Clarinet static class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ClarinetTest extends TestCase {

	public static function setUpBeforeClass() {
		Generator::generate();
	}

	protected function setUp() {
		Db::setUp();
	}

	protected function tearDown() {
		Db::tearDown();
	}

	public function testInit() {
		global $dynTarget;
		$pdo = new PDO('sqlite::memory:');

		Clarinet::init($pdo, $dynTarget);
	}

	public function testGetAllProperty() {

		// Instantiate a persister
		global $dynTarget;
		$director = new PersisterCompanionDirector();
		$loader = new CompanionLoader($director, $dynTarget);
		$persister = $loader->get('zpt\orm\test\mock\SimpleEntity');

		$entity = new SimpleEntity();
		$entity->setName('entity1');
		$entity->setValue('value1');
		$persister->save($entity);

		$entities = Clarinet::getAll('zpt\orm\test\mock\SimpleEntity', 'name');
		$this->assertInternalType('array', $entities);
		$this->assertCount(1, $entities);
		$this->assertArrayHasKey('entity1', $entities);
	}

	public function testGetAllFunction() {
		// Instantiate a persister
		global $dynTarget;
		$director = new PersisterCompanionDirector();
		$loader = new CompanionLoader($director, $dynTarget);
		$persister = $loader->get('zpt\orm\test\mock\SimpleEntity');

		$entity = new SimpleEntity();
		$entity->setName('entity1');
		$entity->setValue('value1');
		$persister->save($entity);

		$fn = function ($entity) {
			return $entity->getName();
		};
		$entities = Clarinet::getAll('zpt\orm\test\mock\SimpleEntity', $fn);
		$this->assertInternalType('array', $entities);
		$this->assertCount(1, $entities);
		$this->assertArrayHasKey('entity1', $entities);
	}

}
