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
 * @package clarinet/test
 */
namespace clarinet\test;

use \PDO;

use \clarinet\PdoWrapper;

use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/test-common.php';

/**
 * This class test the PdoWrapper class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test
 */
class PdoWrapperTest extends TestCase {

  public function testLifeCycle() {
    $pdo = new PDO('sqlite::memory:');

    PdoWrapper::set($pdo);

    $wrapper = PdoWrapper::get();
    $wrapper->close();

    $this->assertNull($pdo);
  }
}
