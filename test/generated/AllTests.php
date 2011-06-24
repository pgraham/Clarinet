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
 */
namespace clarinet\test\generated;

use \PHPUnit_Framework_TestSuite as TestSuite;

require_once __DIR__ . '/../test-common.php';

/**
 * This class builds a suite consisting of all tests for clarinet's generated
 * classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class AllTests {

  public static function suite() {
    $suite = new TestSuite('All Clarinet Generated Classes Tests');

    $suite->addTestSuite('clarinet\test\generated\SimpleEntityPersisterTest');
    $suite->addTestSuite('clarinet\test\generated\ManyToOnePersisterTest');
    $suite->addTestSuite('clarinet\test\generated\OneToManyPersisterTest');
    $suite->addTestSuite('clarinet\test\generated\ManyToManyPersisterTest');
    $suite->addTestSuite(
      'clarinet\test\generated\OneToManyMirrorPersisterTest');
    $suite->addTestSuite(
      'clarinet\test\generated\ManyToManyMirrorPersisterTest');

    return $suite;
  }
}