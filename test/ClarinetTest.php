<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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
namespace zpt\orm;

use \PHPUnit_Framework_TestCase as TestCase;
use \PDO;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests the Clarinet static class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ClarinetTest extends TestCase {

	public function testInit() {
		$pdo = new PDO('sqlite::memory:');

		Clarinet::init($pdo);
	}

}
