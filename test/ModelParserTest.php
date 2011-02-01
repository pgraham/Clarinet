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

use \clarinet\ModelParser;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/test-common.php';

/**
 * This class tests the ModelParser class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test
 */
class ModelParserTest extends TestCase {
  
  /**
   * Tests that the static parse(...) method generates the expected array
   * structure for clarinet's built in ConfigValue model.
   */
  public function testConfigValueModelParser() {
    $modelInfo = ModelParser::parse('clarinet\model\ConfigValue');
    $msg = print_r($modelInfo, true);

    $this->assertInternalType('array', $modelInfo, $msg);
    $this->assertArrayHasKey('class', $modelInfo, $msg);
    $this->assertArrayHasKey('table', $modelInfo, $msg);
    $this->assertArrayHasKey('id', $modelInfo, $msg);
    $this->assertArrayHasKey('properties', $modelInfo, $msg);

    $this->assertEquals('clarinet\model\ConfigValue', $modelInfo['class'],
      $msg);
    $this->assertEquals('config_values', $modelInfo['table'], $msg);

    $properties = $modelInfo['properties'];
    $this->assertInternalType('array', $properties, $msg);
    $name = null;
    $value = null;
    foreach ($properties AS $property) {
      $this->assertInternalType('array', $property, $msg);
      $this->assertArrayHasKey('name', $property, $msg);
      $this->assertArrayHasKey('column', $property, $msg);

      if ($property['name'] == 'Name') {
        $name = $property;
      } else if ($property['name'] == 'Value') {
        $value = $property;
      }
    }
    $this->assertNotNull($name, $msg);
    $this->assertNotNull($value, $msg);
    $this->assertEquals('name', $name['column'], $msg);
    $this->assertEquals('value', $value['column'], $msg);

    $id = $modelInfo['id'];
    $this->assertInternalType('array', $id, $msg);
    $this->assertArrayHasKey('name', $id, $msg);
    $this->assertArrayhasKey('column', $id, $msg);
    $this->assertEquals('Id', $id['name'], $msg);
    $this->assertEquals('id', $id['column'], $msg);
  }

  /**
   * Tests that the static prepareModelInfoForTemplate method generates the
   * expected array structure for clarinet's built in ConfigValue model.
   */
  public function testConfigValueTemplateValues() {
    $modelInfo = ModelParser::parse('clarinet\model\ConfigValue');
    $templateValues = ModelParser::prepareModelInfoForTemplate($modelInfo);
    $msg = print_r($templateValues, true);

    $this->assertInternalType('array', $templateValues, $msg);
    $this->assertArrayHasKey('${persisterName}', $templateValues, $msg);
    $this->assertArrayHasKey('${table}', $templateValues, $msg);
    $this->assertArrayHasKey('${column_names}', $templateValues, $msg);
    $this->assertArrayHasKey('${value_names}', $templateValues, $msg);
    $this->assertArrayHasKey('${sql_setters}', $templateValues, $msg);
    $this->assertArrayHasKey('${id_column}', $templateValues, $msg);

    $this->assertEquals('clarinet_model_ConfigValue',
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
