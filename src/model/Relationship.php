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
 * @package clarinet/model
 */
namespace clarinet\model;

/**
 * Interface for relationship representations.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
interface Relationship {

  /**
   * This method is responsible for returning the clarinet\model\Model object for
   * the entity on the left side of the relationship.
   *
   * @return Model
   */
  public function getLhs();

  /**
   * This method is responsible for returning the name of the property on the
   * left side that contains this relationship.
   *
   * @return string Property name.
   */
  public function getLhsProperty();

  /**
   * This method is responsible for returning the clarinet\model\Model object for
   * the entity on the right side of the relationship.
   *
   * @return Model
   */
  public function getRhs();
}
