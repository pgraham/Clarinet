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

use \clarinet\Criteria;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests the Criteria class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test
 */
class CriteriaTest  extends TestCase {

  /**
   * Tests that the addEquals() method outputs the proper conditions.
   */
  public function testAddEquals() {
    $c = new Criteria();
    $c->addEquals('name', 'aName');
    $output = $c->__toString();
    $expected = "WHERE name = 'aName'";

    $this->assertEquals($expected, $output);

    $c = new Criteria();
    $c->addEquals('count', 1);
    $output = $c->__toString();
    $expected = 'WHERE count = 1';

    $this->assertEquals($expected, $output);
  }
}
