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
namespace zeptech\orm\generator;

use \zeptech\orm\generator\model\Model;
use \zeptech\orm\generator\transformer\ClassBuilder;

/**
 * Generator for model transformer classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class TransformerGenerator extends AbstractGenerator {

  /**
   * Creates a new TransformerGenerator that outputs generated classes to
   * $outputPath/clarinet/transformer
   *
   * @param string $outputPath The base output path for generated files.  Class
   *   files will be output in sub directories of this class that will allow
   *   the class to be namespaced, and autoloaded properly.
   */
  public function __construct($outputPath) {
    parent::__construct($outputPath . '/zeptech/dynamic/orm/transformer');
  }

  /**
   * Generates the PHP Code for a transformer actor for the given model
   * structure.
   *
   * @param Model $model Information about the model for which a transformer
   *   is to be generated.
   * @return string The PHP code for a transformer.
   */
  protected function _generate(Model $model) {
    return ClassBuilder::build($model);
  }
}
