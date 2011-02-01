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

/**
 * Autoloader for clarinet test classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test
 */
class Autoloader {

  /* This is the base path where Clarinet test files are found. */
  private static $_basePath = __DIR__;

  /**
   * Autoload function for Clarinet class files.
   *
   * @param {string} The name of the class to load.
   */
  public static function loadClass($className) {
    // Make sure this is a Clarinet test class
    if (substr($className, 0, 14) != 'clarinet\\test\\') {
      return;
    }

    $logicalPath = str_replace('\\', '/', substr($className, 14));
    $fullPath = self::$_basePath . '/' . $logicalPath . '.php';
    if (file_exists($fullPath)) {
      require_once $fullPath;
    }
  }
}
spl_autoload_register(array('clarinet\test\Autoloader', 'loadClass'));
