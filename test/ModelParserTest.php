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

use \clarinet\model\Parser;
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
   * structure for a mock\SimpleEntity model.
   */
  public function testParseSimpleEntity() {
    $parser = new Parser('clarinet\test\mock\SimpleEntity');
    $info = $parser->parse();
    $msg = print_r($info, true);

    $this->assertInstanceOf('clarinet\model\Info', $info, $msg);

    $this->assertEquals('clarinet\test\mock\SimpleEntity', $info->getClass(),
      $msg);
    $this->assertEquals('config_values', $info->getTable(), $msg);

    $properties = $info->getProperties();
    $this->assertInternalType('array', $properties, $msg);
    $name = null;
    $value = null;
    foreach ($properties AS $property) {
      $this->assertInstanceOf('clarinet\model\Property', $property, $msg);

      if ($property->getName() == 'Name') {
        $name = $property;
      } else if ($property->getName() == 'Value') {
        $value = $property;
      }
    }
    $this->assertNotNull($name, $msg);
    $this->assertNotNull($value, $msg);
    $this->assertEquals('name', $name->getColumn(), $msg);
    $this->assertEquals('value', $value->getColumn(), $msg);

    $id = $info->getId();;
    $this->assertInstanceOf('clarinet\model\Property', $id, $msg);
    $this->assertEquals('Id', $id->getName(), $msg);
    $this->assertEquals('id', $id->getColumn(), $msg);
  }
}
