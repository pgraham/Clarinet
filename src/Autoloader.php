<?php
namespace Clarinet;

class Autoloader {

  /* This is the base path where Clarinet source files are found. */
  private static $_basePath = __DIR__;

  /**
   * Autoload function for Clarinet class files.
   *
   * @param {string} The name of the class to load.
   */
  public static function loadClass($className) {
    // Make sure this is a Clarinet class
    if (substr($className, 0, 8) != 'Clarinet') {
      return;
    }

    $logicalPath = str_replace('\\', '/', $className);
    $fullPath = self::$_basePath . '/' . $logicalPath . '.php';
    if (file_exists($fullPath)) {
      require_once $fullPath;
    }
  }
}

spl_autoload_register(array('Clarinet\Autoloader', 'loadClass'));
