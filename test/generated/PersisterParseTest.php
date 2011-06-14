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

use \clarinet\test\mock\SimpleEntity;
use \clarinet\PersisterGenerator;

use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../test-common.php';

/**
 * This class tests a generated persister for a simple entity that contains only
 * scalar value columns.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/generated
 */
class SimpleEntityPersisterTest extends TestCase {

  public function testCreate() {
    $mockDir = __DIR__ . '/../mock';
    $generator = new PersisterGenerator($mockDir . '/gen');
    $generator->generate('clarinet\test\mock\SimpleEntity');
  }
}
