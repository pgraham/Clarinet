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
namespace zeptech\orm\runtime;

/**
 * Static interface for retrieving Validator model actors.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Validator {

  /**
   * Retrieve the validator instance for the given model instance of class name.
   *
   * @param mixed $model Either an instance of the model or the name of a model
   *   for which to retrieve a validator.
   * @return Validator
   */
  public static function get($model) {
    if (is_object($model)) {
      $model = get_class($model);
    }

    return ActorFactory::getActor('validator', $model);
  }
}
