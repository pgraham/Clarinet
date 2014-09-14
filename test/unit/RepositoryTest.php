<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\orm;

use PHPUnit_Framework_TestCase as TestCase;

use zpt\opal\CompanionLoaderFactory;
use zpt\orm\test\Db;
use zpt\orm\test\Generator;

require_once __DIR__ . '/../setup.php';

/**
 * This class tests the {@link Repository} class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class RepositoryTest extends TestCase
{

	/**
		* Suite wide setUp, ensure that all Mock classes have had their actor's
		* generated.
		*/
	public static function setUpBeforeClass() {
		Generator::generate();
	}

	private $db;
	private $ldrFactory;

	protected function setUp() {
		$this->db = Db::setUp();

		// Instantiate a persister
		global $dynTarget;
		$this->ldrFactory = new CompanionLoaderFactory($dynTarget);
	}

	public function testGetPersister() {
		$orm = new Repository($this->db, $this->ldrFactory);

		$persister = $orm->getPersister('zpt\orm\test\mock\SimpleEntity');
		$this->assertNotNull($persister);
	}
}
