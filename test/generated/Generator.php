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
namespace clarinet\test\generated;

use \DirectoryIterator;
use \ReflectionClass;

use \clarinet\Autoloader;
use \clarinet\PersisterGenerator;
use \clarinet\TransformerGenerator;
use \clarinet\ValidatorGenerator;

use \reed\reflection\ReflectionHelper;

/**
 * This class generates actors for all mock entities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Generator {

  /**
   * Iterator over all files in the mock directory and create actors for any
   * entity classes.
   */
  public static function generate() {
    $mockDir = __DIR__ . '/../mock';
    $persisterGen = new PersisterGenerator($mockDir . '/gen');
    $transformerGen = new TransformerGenerator($mockDir . '/gen');
    $validatorGen = new ValidatorGenerator($mockDir . '/gen');

    $dir = new DirectoryIterator($mockDir);
    foreach ($dir AS $file) {
      if ($file->isDot() || $file->isDir()) {
        continue;
      }

      $filename = $file->getFilename();
      if (substr($filename, -4) != '.php') {
        continue;
      }
      
      $className = "clarinet\\test\\mock\\" . substr($filename, 0, -4);
      $refClass = new ReflectionClass($className);
      $annotations = ReflectionHelper::getAnnotations(
        $refClass->getDocComment());

      if (isset($annotations['entity'])) {
        $persisterGen->generate($className);  
        $transformerGen->generate($className);
        $validatorGen->generate($className);
      }
    }

    // Set the clarinet Autoloader to point to the generated classes
    $genDir = $mockDir . '/gen/clarinet';
    Autoloader::$genBasePath = $genDir;
  }
}
