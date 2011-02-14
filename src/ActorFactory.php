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

/**
 * Instances of this class create model actors of a specified type for all
 * model classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class ActorFactory {

  /* The type of actor instantiated by this factory */
  private $_actorType;

  /* Cache of already loaded actors indexed by model class */
  private $_cache = Array();

  /**
   * Create a new ActorFactory for actors of the given type.
   *
   * @param string $actorType The type of actor created by this factory.
   */
  public function __construct($actorType) {
    $this->_actorType = $actorType;
  }

  /**
   * Clears the cache of actors.  This is mostly used to clear out the cache of
   * persisters between unit tests since the PDO connection is nullified at the
   * end of a test.
   */
  public function clearCache() {
    $this->_cache = Array();
  }

  /**
   * Get an actor for the given class.  If this is the first time that the actor
   * has been accessed for the class and DEBUG is defined and set to true then
   * the actor will be regenerated.
   *
   * @param string $modelClass The name of the model that the actor acts upon.
   * @return object
   */
  public function getActor($modelClass) {
    if (!isset($this->_cache[$modelClass])) {
      $this->_cache[$modelClass] = $this->_load($modelClass);
    }
    return $this->_cache[$modelClass];
  }

  /*
   * Generate if in DEBUG and return an instance of the actor for the specified
   * model class.
   */
  private function _load($modelClass) {
    if (defined('DEBUG') && DEBUG === true) {
      ActorGenerator::generate($this->_actorType, $modelClass);
    }

    $actorClass = str_replace('\\', '_', $modelClass);
    $fullyQualified = 'clarinet\\' . $this->_actorType . '\\' . $actorClass;
    $actor = new $fullyQualified();
    return $actor;
  }
}
