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
 * @package clarinet/test/mock
 */
namespace clarinet\test\mock;

/**
 * Entity that is the Right hand side of a one-to-many relationship only
 * declared on the 'one' side of the relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/mock
 *
 * @Entity(table = one_to_many_rhs)
 */
class OneToManyRhs {

  private $_id;
  private $_name;

  /**
   * @Id
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * @Column
   */
  public function getName() {
    return $this->_name;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setName($name) {
    $this->_name = $name;
  }
}
