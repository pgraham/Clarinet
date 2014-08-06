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
namespace zpt\orm\test;

use \zpt\orm\Clarinet;
use \zpt\orm\PdoWrapper;
use \PDO;

/**
 * This class provides setUp functionality common to all tests that require
 * the mock database.  The mock database is a database that contains the
 * structure described by the mock model objects.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Db {

  /**
   * This method overwrites any current mock database with the clean template
   * and creates a PDO connection to it.
   *
   * @return PDO PDO connection to the mock database.
   */
  public static function setUp() {
    global $dynTarget;

    copy(__DIR__ . '/mock_db.template.sq3', __DIR__ . '/../gen/db.sq3');

    $pdo = new PDO('sqlite:' . __DIR__ . '/../gen/db.sq3');
    Clarinet::init($pdo, $dynTarget);
    //PdoWrapper::set($pdo);
  }

  /**
   * This method cleans up the connection to the database
   *
   * @param PDO PDO connection to clean up.
   */
  public static function tearDown() {
    Clarinet::reset();
    unlink(__DIR__ . '/../gen/db.sq3');
  }
}
