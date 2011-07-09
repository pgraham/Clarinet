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
namespace clarinet\persister;

use \clarinet\model\Property;

/**
 * This class is responsible for building getter/setter code for different types
 * of properties.
 *
 * TODO Once the transformer has been expanded to perform db-model
 *      transformations for properties, this class can be eliminated.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PropertyBuilder {

  /* The property for which code is generated */
  private $_property;

  /**
   * Create a new property code builder for the given property.
   *
   * @param Property $property
   */
  public function __construct(Property $property) {
    $this->_property = $property;
  }

  /**
   * Get the setter code for the property.
   *
   * @param string $modelVar The name of the variable that contains the model
   *   whose property to set.
   * @param string $rowVar The name of the variable that contains the db row
   *   from which to retrieve the property value.
   * @return string
   */
  public function populateFromDb($modelVar, $rowVar) {
    $name = $this->_property->getName();
    $type = $this->_property->getType();
    $col = $this->_property->getColumn();

    switch ($type) {
      case Property::TYPE_BOOLEAN:
      return "\${$modelVar}->set$name"
        . "(\${$rowVar}['$col'] == 1 ? true : false);";

      case Property::TYPE_INTEGER:
      return "\${$modelVar}->set$name((int) \${$rowVar}['$col']);";

      default:
      return "\${$modelVar}->set$name(\${$rowVar}['$col']);";
    }
  }
}
