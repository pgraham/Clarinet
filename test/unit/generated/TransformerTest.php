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

use \zpt\opal\CompanionLoader;
use \zpt\orm\companion\TransformerCompanionDirector;
use \zpt\orm\test\mock\SimpleEntity;
use \zpt\orm\test\Generator;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../../setup.php';

/**
 * This class tests transformer companion functionality.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class TransformerTest extends TestCase {

  public static function setUpBeforeClass() {
    Generator::generate();
  }

  private $transformer;

  protected function setUp() {
    global $dynTarget;
    $loader = new CompanionLoader('transformer', $dynTarget);
    $this->transformer = $loader->get('zpt\orm\test\mock\SimpleEntity');
  }

  public function testAsArray() {
    $entity = new SimpleEntity();
    $entity->setName('MyEntity');
    $entity->setValue('EntityValue');

    $transformed = $this->transformer->asArray($entity);

    $this->assertArrayHasKey('name', $transformed);
    $this->assertArrayHasKey('value', $transformed);
  }
}
