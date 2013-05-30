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
namespace zpt\orm;

use \zeptech\orm\generator\model\Model;
use \zpt\opal\CompanionGenerator;
use \zpt\orm\model\ModelCache;

/**
 * This class provides functionality common to all persisters.  This includes
 * Parsing the model and output the generated code to a specified output
 * directory.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class ModelCompanionGenerator extends CompanionGenerator {

  private $modelCache;

  public function setModelCache(ModelCache $modelCache) {
    $this->modelCache = $modelCache;
  }

  /**
   * Generate the code.  This method delegates to the implementation for the
   * acutal generation then outputs to the specified path.
   *
   * @param string $className The entity for which to generate code.
   */
  protected function getValues($className) {
    $model = $this->modelCache->get($className);
    return $this->getValuesForModel($model);
  }

  /**
   * This method is responsible for actually generating the actor code.
   *
   * @param Model $model Parsed model information.
   * @return string The PHP code for the generated actor.
   */
  protected abstract function getValuesForModel(Model $model);

}
