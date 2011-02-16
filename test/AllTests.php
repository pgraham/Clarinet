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

use \PHPUnit_Framework_TestSuite as TestSuite;

require_once __DIR__ . '/test-common.php';

/**
 * This class build a suite consisting of all tests for clarinet.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test
 */
class AllTests {

  public static function suite() {
    $suite = new TestSuite('All Clarinet Tests');

    $suite->addTestSuite('clarinet\test\CriteriaTest');
    $suite->addTestSuite('clarinet\test\PdoWrapperTest');
    $suite->addTestSuite('clarinet\test\model\ParserTest');

    $suite->addTestSuite('clarinet\test\generated\SimpleEntityPersisterTest');
    $suite->addTestSuite('clarinet\test\generated\ManyToOnePersisterTest');
    $suite->addTestSuite('clarinet\test\generated\OneToManyPersisterTest');
    $suite->addTestSuite('clarinet\test\generated\ManyToManyPersisterTest');
    $suite->addTestSuite('clarinet\test\generated\OneToManyMirrorPersisterTest');

    return $suite;
  }
}
