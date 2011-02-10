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
 * This class encapsulates a one-to-many relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class OneToMany implements Relationship {

  private $_rhs;
  private $_property;
  private $_column;

  /**
   * Creates a new one-to-many relationship representation.
   *
   * @param string $rhs The name of the entity on the right side of the
   *   relationship.
   * @param string $property The name of the property in the left hand side
   *   entity that contains the related right hand side instances.
   * @param string $column The name of the column in the right side of the
   *   relationship that contains the id of the right side entity to which
   *   left side entities are related.
   */
  public function __construct($rhs, $property, $column) {
    $this->_rhs = $rhs;
    $this->_property = $property;
    $this->_column = $column;
  }

  /**
   * Returns the code for populating the left hand side of the relationship with
   * the collection of entities from the 'many' side.
   *
   * @return string PHP Code that will populate the model with the collection of
   *   related entities.
   */
  public function getPopulateCode() {
    $templateValues = Array
    (
      '${rhs}'          => $this->_rhs,
      '${rel_property}' => $this->_property,
      '${rel_column}'   => $this->_column
    );

    // Use the instance cache since its likely that the template has already
    // been loaded for another relationship of the same type.
    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('one-to-many', $templateValues);
    return $code;
  }
}
