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

use \zeptech\orm\generator\model\Parser;
use \SplFileObject;

/**
 * Generator for actor classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ActorGenerator {

  /**
   * Generate the actor of the given type for the given model class.  The code
   * is output at the following path:
   *
   *   Clarinet::$outputPath . '/zeptech/dynamic/orm/<actor-name>/<actor-class-name>
   *
   * where <actor-class-name> is the fully qualified name of the model class
   * with backslashes (\) replaced with underscores (_).
   *
   * @param string $actorType The type of actor to generate.
   * @param string $className The name of the model class for which to generate
   *   the actor.
   */
  public static function generate($actorType, $modelClass) {
    $generatorType = "clarinet\\" . ucfirst($actorType) . "Generator";
    $generator = new $generatorType(Clarinet::$outputPath);
    $generator->generate($modelClass);
  }
}
