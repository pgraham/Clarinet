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
 */
class ActorFactory {

  /* The type of actor instantiated by this factory */
  private $_actorType;

  /* Cache of already loaded actors indexed by model class */
  private $_cache = Array();

  public function __construct($actorType) {
    $this->_actorType = $actorType;
  }

  public function getActor($modelClass) {
    if (!isset($this->_cache[$modelClass])) {
      $this->_cache[$modelClass] = $this->_load($modelClass);
    }
    return $this->_cache[$modelClass];
  }

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
