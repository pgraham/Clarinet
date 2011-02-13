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

use \clarinet\TemplateLoader;

/**
 * This class encapsulates a one-to-many relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class OneToMany implements Relationship {

  private $_lhs;
  private $_lhsProperty;
  private $_rhs;
  private $_rhsColumn;

  /**
   * Creates a new one-to-many relationship representation.
   *
   * @param string $lhs The name of the entity on the left side of the
   *   relationship.
   * @param string $rhs The name of the entity on the right side of the
   *   relationship.
   * @param string $property The name of the property in the left hand side
   *   entity that contains the related right hand side instances.
   * @param string $column The name of the column in the right side of the
   *   relationship that contains the id of the left side entity to which
   *   right side entities are related.
   */
  public function __construct($lhs, $rhs, $lhsProperty, $rhsColumn) {
    $this->_lhs = $lhs;
    $this->_lhsProperty = $lhsProperty;
    $this->_rhs = $rhs;
    $this->_rhsColumn = $rhsColumn;
  }

  /**
   * Returns the code for populating the left hand side of the relationship with
   * the collection of entities from the 'many' side.
   *
   * @return string PHP Code that will populate the model with the collection of
   *   related entities.
   */
  public function getPopulateModelCode() {
    $templateValues = Array
    (
      '${rhs}'          => $this->_rhs,
      '${rhs_column}'   => $this->_rhsColumn,
      '${lhs_property}' => $this->_lhsProperty
    );

    // Use the instance cache since its likely that the template has already
    // been loaded for another relationship of the same type.
    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('one-to-many-model', $templateValues);
    return $code;
  }

  /**
   * A one-to-many relationship does not store any information in the left hand
   * side so that there is nothing to do here.
   */
  public function getPopulateParameterCode() {
    return null;
  }

  /**
   * A one-to-many relationship does not store any information in the left hand
   * side so there is nothing to do here.
   */
  public function getLhsColumnName() {
    return null;
  }
}
