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
namespace zpt\orm\test\mock;

/**
 * Mock class that declares a many-to-many relationship with a
 * mock\SimpleEntity.  The relationship is not declared on the other side.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/mock
 *
 * @Entity(table = many_to_many_entity)
 */
class ManyToManyEntity {

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
   * @Column(name = name)
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * @ManyToMany(entity = zpt\orm\test\mock\SimpleEntity)
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

  public function setMany(Array $many) {
    $this->_many = $many;
  }
}
