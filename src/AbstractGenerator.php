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

use \clarinet\model\Info;
use \clarinet\model\Parser;

/**
 * This class provides functionality common to all persisters.  This includes
 * Parsing the model and output the generated code to a specified output
 * directory.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
abstract class AbstractGenerator {

  private $_outputPath;

  /**
   * Create a new generator that outputs to the given path.
   *
   * @param string $outputPath The path for where to output the code.  This
   *   path must be writable by the current user.
   */
  public function __construct($outputPath) {
    $this->_outputPath = $outputPath;
    if (substr($this->_outputPath, -1) == '/') {
      $this->_outputPath = substr($this->_outputPath, 0, -1);
    }
  }

  /**
   * Generate the code.  This method delegates to the implementation for the
   * acutal generation then outputs to the specified path.
   *
   * @param string $className The entity for which to generate code.
   */
  public function generate($className) {
    $modelInfo = Parser::getModelInfo($className);
    $classBody = $this->_generate($modelInfo);

    if (!file_exists($this->_outputPath)) {
      mkdir($this->_outputPath, 0755, true);
    }
    $fileName = str_replace('\\', '_', $modelInfo->getClass()) . '.php';

    $fullPath = $this->_outputPath . '/' . $fileName;
    $file = new SplFileObject($fullPath, 'w');
    $file->fwrite($classBody);
  }

  /**
   * This method is responsible for actually generating the actor code.
   *
   * @param Info $modelInfo Parsed model information.
   * @return string The PHP code for the generated actor.
   */
  protected abstract function _generate(Info $modelInfo);
}
