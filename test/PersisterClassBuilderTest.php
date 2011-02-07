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

use PHPUnit_Framework_TestCase as TestCase;

/**
 * This class tests the clarinet\persister\ClassBuilder class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package
 */
class PersisterClassBuilderTest extends TestCase {

  /**
   * Tests that the static prepareModelInfoForTemplate method generates the
   * expected array structure for a mock\SimpleEntity model.
   */
  public function testSimpleEntityTemplateValues() {
    $parser = new Parser('clarinet\test\mock\SimpleEntity');
    $info = $parser->parse();
    $templateValues = $parser->getTemplateValues();
    $msg = print_r($templateValues, true);

    $this->assertInternalType('array', $templateValues, $msg);
    $this->assertArrayHasKey('${persisterName}', $templateValues, $msg);
    $this->assertArrayHasKey('${table}', $templateValues, $msg);
    $this->assertArrayHasKey('${column_names}', $templateValues, $msg);
    $this->assertArrayHasKey('${value_names}', $templateValues, $msg);
    $this->assertArrayHasKey('${sql_setters}', $templateValues, $msg);
    $this->assertArrayHasKey('${id_column}', $templateValues, $msg);

    $this->assertEquals('clarinet_test_mock_SimpleEntity',
      $templateValues['${persisterName}'], $msg);
    $this->assertEquals('config_values', $templateValues['${table}'], $msg);
    $this->assertEquals('name,value', $templateValues['${column_names}'], $msg);
    $this->assertEquals(':name,:value', $templateValues['${value_names}'],
      $msg);
    $this->assertEquals('name = :name,value = :value',
      $templateValues['${sql_setters}'], $msg);
    $this->assertEquals('id', $templateValues['${id_column}'], $msg);
  }
}
