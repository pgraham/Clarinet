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
 * Mocks an entity that declares a one-to-many relationship that is mirrored
 * on the other side of the relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 *
 * @Entity(table = one_to_many_mirror)
 */
class OneToManyMirrorEntity {

  private $_id;
  private $_name;
  private $_many;

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
   * @OneToMany(entity = zpt\orm\test\mock\ManyToOneMirrorEntity)
   */
  public function getMany() {
    return $this->_many;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setName($name) {
    $this->_name = $name;
  }

  public function setMany(array $many) {
    $this->_many = $many;
  }
}
