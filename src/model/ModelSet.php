<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
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
namespace clarinet\model;

use \Iterator;

/**
 * This class encapsulates a set of related ModelInfo objects.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelSet implements Iterator {

  /* The list of models in the set */
  private $_models = Array();

  /**
   * Create a new ModelSet for the given list of model names.
   *
   * @param Array $modelNames The list of model class names that compose the
   *   set.
   */
  public function __construct(Array $modelNames) {
    foreach ($modelNames AS $modelName) {
      $this->_models[] = Parser::getModel($modelName);
    }
  }

  /*
   * ===========================================================================
   * Iterator implementation.
   * ===========================================================================
   */

  public function current() {
    return current($this->_models);
  }

  public function key() {
    return key($this->_models);
  }

  public function next() {
    next($this->_models);
  }

  public function rewind() {
    reset($this->_models);
  }

  public function valid() {
    // This is safe to do since we know that the _models array will not contain
    // any false elements
    return current($this->_models) !== false;
  }
}
