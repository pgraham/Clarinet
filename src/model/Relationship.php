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
   * This method is responsible for returning the code that will populate an
   * instance of the left hand side with the right hand side.
   */
  public function getPopulateModelCode();

  /**
   * This method is responsible for returning code that will populate a PDO
   * parameter name with the right-hand side id.  This is for update and
   * insert statements.
   */
  public function getPopulateParameterCode();

  /**
   * This method is responsible for returning the name of a column to include in
   * SQL CREATE and UPDATE statements of the left hand side of the relationship
   * or NULL if there is none.
   */
  public function getLhsColumnName();
}
