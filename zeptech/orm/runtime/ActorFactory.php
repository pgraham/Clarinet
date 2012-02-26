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
 * Instances of this class create model actors of a specified type for all
 * model classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ActorFactory {

  /* Loaded factories, indexed by actor type */
  private static $_factories = Array();

  /**
   * Clears the cache of each loaded factory and then clears the cache of loaded
   * factories.  This is generally run as part of the tearDown method of a unit
   * test.
   */
  public static function clearFactories() {
    foreach (self::$_factories AS $factory) {
      $factory->_clearCache();
    }
    self::$_factories = Array();
  }

  /**
   * This methods first loads a factory for the given type of actor which is
   * then used to create an actor instance for the given model class.
   *
   * @param string $actorType The type of action to retrieve
   * @param string $modelClass The model class that the actor acts upon
   * @return Actor of the specified type for the given type of model
   */
  public static function getActor($actorType, $modelClass) {
    $factory = self::getFactory($actorType);
    $actor = $factory->_getActor($modelClass);
    return $actor;
  }

  /**
   * This method loads and returns a factory for the given type of actor.
   *
   * @param string $actorType The type of actor factory to instantiate
   */
  public static function getFactory($actorType) {
    if (!isset(self::$_factories[$actorType])) {
      self::$_factories[$actorType] = new ActorFactory($actorType);
    }
    return self::$_factories[$actorType];
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  /* The type of actor instantiated by this factory */
  private $_actorType;

  /* Cache of already loaded actors indexed by model class */
  private $_cache = Array();

  /*
   * Create a new ActorFactory for actors of the given type.
   *
   * @param string $actorType The type of actor created by this factory.
   */
  private function __construct($actorType) {
    $this->_actorType = $actorType;
  }

  /*
   * Clears the cache of actors.  This is mostly used to clear out the cache of
   * persisters between unit tests since the PDO connection is nullified at the
   * end of a test.
   */
  private function _clearCache() {
    $this->_cache = Array();
  }

  /*
   * Get an actor for the given class.  If this is the first time that the actor
   * has been accessed for the class and DEBUG is defined and set to true then
   * the actor will be regenerated.
   *
   * @param string $modelClass The name of the model that the actor acts upon.
   * @return object
   */
  private function _getActor($modelClass) {
    if (!isset($this->_cache[$modelClass])) {
      $this->_cache[$modelClass] = $this->_load($modelClass);
    }
    return $this->_cache[$modelClass];
  }

  /*
   * Return an instance of the actor for the
   * specified model class.
   */
  private function _load($modelClass) {
    $ns = "zeptech\\dynamic\\clarinet\\$this->_actorType";
    $actorClass = str_replace('\\', '_', $modelClass);
    $fullyQualified = "$ns\\$actorClass";
    $actor = new $fullyQualified();
    return $actor;
  }
}
