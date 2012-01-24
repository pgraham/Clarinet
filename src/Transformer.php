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
namespace clarinet;

/**
 * Static interface for Transformer model actors.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Transformer {

  /**
   * Retrieve the transformer instance for the given model instance or class
   * name.
   *
   * @param mixed $model Either an instance of the model or the name of a model
   *   for which to retrieve a transformer.
   * @return Transformer
   */
  public static function get($model) {
    if (is_object($model)) {
      $model = get_class($model);
    }

    return ActorFactory::getActor('transformer', $model);
  }
}
