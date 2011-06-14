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
    $c = $this->_createBaseCriteria();

    $c->addEquals('name', 'aName');
    $expected = "SELECT `simple_entity`.* FROM `simple_entity` WHERE `name` = :param0";
    $this->_assertCriteriaOutput($c, $expected);

    $params = $c->getParameters();
    $this->assertInternalType('array', $params);
    $this->assertArrayHasKey(':param0', $params);
    $this->assertEquals('aName', $params[':param0']);
  }

  public function testAddSorts() {
    $c = $this->_createBaseCriteria();
    $c->addSort('col1');
    $expected = "SELECT `simple_entity`.* FROM `simple_entity` ORDER BY `col1`";
    $this->_assertCriteriaOutput($c, $expected);

    $c = $this->_createBaseCriteria();
    $c->addSort('col1, col2');
    $expected = "SELECT `simple_entity`.* FROM `simple_entity` ORDER BY `col1`,`col2`";
    $this->_assertCriteriaOutput($c, $expected);

    $c = $this->_createBaseCriteria();
    $c->addSort(array('col1', 'col2'));
    $expected = "SELECT `simple_entity`.* FROM `simple_entity` ORDER BY `col1`,`col2`";
    $this->_assertCriteriaOutput($c, $expected);

    $c = $this->_createBaseCriteria();
    $c->addSort('messed`up``column```name');
    $expected = "SELECT `simple_entity`.* FROM `simple_entity` ORDER BY `messed``up````column``````name`";
    $this->_assertCriteriaOutput($c, $expected);
  }

  private function _createBaseCriteria() {
    $c = new Criteria();
    $c->setTable('simple_entity');
    return $c;
  }

  private function _assertCriteriaOutput($c, $expected) {
    $output = $c->__toString();
    $this->assertEquals($expected, $output);
  }
}
