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

use \clarinet\transformer\ClassBuilder;

/**
 * Generator for model transformer classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class TransformerGenerator {

  /**
   * Generate the transformer code for the given model class.  The code is
   * output at the following path:
   *
   *   Clarinet::$outputPath . '/clarinet/transformer/<transformer-class-name>
   *
   * where <transformer-class-name> is the fully qualified name of the model
   * class with backslashes (\) replaced with underscores (_).
   *
   * @param string $className The name of the model class for which to generate
   *   a transformer.
   * @return string The code for performing various transformations on instances
   *   of the given model class.
   */
  public static function generate($className) {
    $modelInfo = ModelParser::parse($className);
    $classBody = ClassBuilder::build($modelInfo);

    $filePath = Clarinet::$outputPath . '/clarinet/transformer';
    if (!file_exists($filePath)) {
      mkdir($filePath, 0755, true);
    }
    $fileName = str_replace('\\', '_', $modelInfo['class']) . '.php';

    $file = new SplFileObject($filePath . '/' . $fileName, 'w');
    $file->fwrite($classBody);
  }
}
