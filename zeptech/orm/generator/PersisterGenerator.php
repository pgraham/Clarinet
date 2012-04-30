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
use \zeptech\orm\generator\persister\PersisterBuilder;

/**
 * Generator for model persisters.  Generated code is output into a file at the
 * path given to the Clarinet::init(...) method.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PersisterGenerator extends AbstractModelGenerator {

  /**
   * Creates a new PersisterGenerator that outputs generated classes to
   * $outputPath/zeptech/dynamic/orm/persister
   *
   * @param string $outputPath The base output path for generated files.  Class
   *   files will be output in sub directories of this class that will allow
   *   the class to be namespaced, and autoloaded properly.
   */
  public function __construct($outputPath) {
    parent::__construct($outputPath . '/zeptech/dynamic/orm/persister');
  }

  /**
   * Generates the PHP Code for a persister actor for the given model
   * structure.
   *
   * @param Model $model Information about the model for which a persister is
   *   to be generated.
   * @return string The PHP code for a persister.
   */
  protected function _generateForModel(Model $model) {
    return PersisterBuilder::build($model);
  }
}
