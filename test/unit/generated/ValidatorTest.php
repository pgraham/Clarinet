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
namespace zpt\orm\unit\generated;

use \zpt\opal\CompanionLoader;
use \zpt\orm\companion\ValidatorCompanionDirector;
use \zpt\orm\test\mock\SimpleEntity;
use \zpt\orm\test\Db;
use \zpt\orm\test\Generator;
use \PHPUnit_Framework_TestCase as TestCase;

require_once __DIR__ . '/../../setup.php';

/**
 * This class tests validator companion functionality.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ValidatorTest extends TestCase {

  public static function setUpBeforeClass() {
    Generator::generate();
  }

  /* Object under test */
  private $validator;

  protected function setUp() {
    global $dynTarget;

    DB::setUp();

    $loader = new CompanionLoader('validator', $dynTarget);
    $this->validator = $loader->get('zpt\orm\test\mock\SimpleEntity');
  }

  public function testValidation() {
    $entity = new SimpleEntity();

    $this->validator->validate($entity);
  }
}
