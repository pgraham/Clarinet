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
 * @package clarinet/persister
 */
namespace clarinet\persister;

/**
 * Interface for the RelationshipBuilder class and its delegate classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/persister
 */
interface RelationshipBuilderI {

  /**
   * This method is responsible for returning the PHP code that will delete the
   * right side of the relationship.
   *
   * @return string PHP code
   */
  public function getDeleteCode();

  /**
   * This method is responsible for returning the PHP code that will populate a
   * left side entity with the entity on the right side of the relationship.
   *
   * @return string PHP code
   */
  public function getRetrieveCode();

  /**
   * This method is reponsible for returning the PHP code that will populate an
   * SQL statement parameter array with any information that is stored in the
   * left side of the relationship about the right side of the relationship.
   *
   * @return string PHP code
   */
  public function getSaveLhsCode();

  /**
   * This method is responsible for returning the PHP code that will save the
   * right side of the relation.
   *
   * @return string PHP code
   */
  public function getSaveRhsCode();
}
