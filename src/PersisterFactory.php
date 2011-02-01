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
 * @package clarinet
 */
namespace clarinet;

/**
 * Factory class for loading model persisters.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class PersisterFactory {

  /* Cache of previously loaded persisters indexed by class name */
  private static $_cache = array();

  /**
   * Retrieve the persister for the specified model class.
   *
   * @param string $className The name of the class for which to load the
   *   persister
   * @return persister instance
   */
  public static function getPersister($className) {
    if (!isset(self::$_cache[$className])) {
      self::_loadPersister($className);
    }
    return self::$_cache[$className];
  }

  /* Load an instance of the persister for the given model class */
  private static function _loadPersister($className) {
    if (defined('DEBUG') && DEBUG === true) {
      PersisterGenerator::generate($className);
    }

    $persisterClass = str_replace('\\', '_', $className);
    $persisterPath = Clarinet::$outputPath . '/clarinet/persister/';
    $fullPath = $persisterPath . $persisterClass . '.php';
    if (file_exists($fullPath)) {
      require_once $fullPath;
    } else {
      throw new Exception("Unable to load persister for $className.  Calculated"
        . "path is $fullPath");
    }
    
    $fullyQualified = "clarinet\\persister\\$persisterClass";
    $persister = new $fullyQualified();
    self::$_cache[$className] = $persister;
  }
}
