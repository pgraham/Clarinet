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
 * Mock class that declares a many-to-one relationship with another entity.  The
 * relationship is not declared at the other side.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Entity(table = many_to_one_entity)
 */
class ManyToOneEntity {

  private $_id;
  private $_name;
  private $_one;

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
   * @ManyToOne(entity = zpt\orm\test\mock\SimpleEntity)
   */
  public function getOne() {
    return $this->_one;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setName($name) {
    $this->_name = $name;
  }

  public function setOne(SimpleEntity $one) {
    $this->_one = $one;
  }
}
