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
use \zeptech\orm\generator\model\Parser;
use \zpt\pct\AbstractGenerator;

/**
 * This class provides functionality common to all persisters.  This includes
 * Parsing the model and output the generated code to a specified output
 * directory.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class AbstractModelGenerator extends AbstractGenerator {

  /**
   * Generate the code.  This method delegates to the implementation for the
   * acutal generation then outputs to the specified path.
   *
   * @param string $className The entity for which to generate code.
   */
  protected function _generate($className) {
    $model = Parser::getModel($className);
    return $this->_generateForModel($model);
  }

  /**
   * This method is responsible for actually generating the actor code.
   *
   * @param Model $model Parsed model information.
   * @return string The PHP code for the generated actor.
   */
  protected abstract function _generateForModel(Model $model);
}
