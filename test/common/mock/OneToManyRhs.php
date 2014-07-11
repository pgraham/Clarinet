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
namespace zpt\orm\test\mock;

/**
 * Entity that is the Right hand side of a one-to-many relationship only
 * declared on the 'one' side of the relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Entity(table = one_to_many_rhs)
 */
class OneToManyRhs {

  private $_id;
  private $_name;
  private $_oneToManyEntityId;

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

  /**
   * @Column(name = one_to_many_entity_id)
   */
  public function getOneToManyEntityId() {
    return $this->_oneToManyEntityId;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setName($name) {
    $this->_name = $name;
  }

  public function setOneToManyEntityId($oneToManyEntityId) {
    $this->_oneToManyEntityId = $oneToManyEntityId;
  }
}
