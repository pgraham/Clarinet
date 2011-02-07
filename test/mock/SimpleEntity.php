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
 * Mock class that represents an entityt with no relationships to other entities
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/test/mock
 *
 * @Entity(table = config_values)
 */
class SimpleEntity {

  private $_id;
  private $_name;
  private $_value;

  /**
   * @Id
   * @Column(name = id)
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
   * @Column(name = value)
   */
  public function getValue() {
    return $this->_value;
  }

  public function setId($id) {
    $this->_id = $id;
  }

  public function setName($name) {
    $this->_name = $name;
  }

  public function setValue($value) {
    $this->_value = $value;
  }
}
