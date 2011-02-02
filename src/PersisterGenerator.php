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

use \SplFileObject;

use \clarinet\persister\ClassBuilder;

/**
 * Generator for model persisters.  Generated code is output into a file at the
 * path given to the Clarinet::init(...) method.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class PersisterGenerator {

  public static function generate($className) {
    $entityInfo = ModelParser::parse($className);
    $classBody = ClassBuilder::build($entityInfo);

    $filePath = Clarinet::$outputPath . '/clarinet/persister';
    if (!file_exists($filePath)) {
      mkdir($filePath, 0755, true);
    }
    $fileName = str_replace('\\', '_', $entityInfo['class']) . '.php';

    $file = new SplFileObject($filePath . '/' . $fileName, 'w');
    $file->fwrite($classBody);
  }
}
