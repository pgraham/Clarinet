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

use \clarinet\model\Info;
use \clarinet\validator\ClassBuilder;

/**
 * Genertor for model validator classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class ValidatorGenerator extends AbstractGenerator {

  /**
   * Creates a new ValidatorGenerator that outputs generated classes to
   * $outputPath/clarinet/validator
   *
   * @param string $outputPath The base output path for generated files.  Class
   *   files will be output in sub directories of this class that will allow
   *   the class to be namespaced, and autoloaded properly.
   */
  public function __construct($outputPath) {
    parent::__construct($outputPath . '/clarinet/validator');
  }


  /**
   * Generates the PHP Code for a validator actor for the given model
   * structure.
   *
   * @param Info $modelInfo Information about the model for which a validator
   *   is to be generated.
   * @return string The PHP code for a validator.
   */
  protected function _generate(Info $modelInfo) {
    return ClassBuilder::build($modelInfo);
  }
}
