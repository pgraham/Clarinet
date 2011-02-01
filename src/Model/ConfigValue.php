<?php
namespace Clarinet\Model;

class ConfigValue {

  private $_name;
  private $_value;

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
}
