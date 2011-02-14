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
  private $_rhs;
  private $_lhsProperty;
  private $_rhsColumn;
  private $_rhsProperty;

  /**
   * Creates a new one-to-many relationship representation.
   *
   * @param string $lhs The name of the entity on the left side of the
   *   relationship.
   * @param string $rhs The name of the entity on the right side of the
   *   relationship.
   * @param string $lhsProperty The name of the property in the left hand side
   *   entity that contains the related right hand side instances.
   * @param string $rhsColumn The name of the column in the right side of the
   *   relationship that contains the id of the left side entity to which
   *   right side entities are related.
   * @param string $rhsProperty The name of the column in the right side of the
   *   relationship that contains the id of the left side entity to which
   *   right side entities are related.
   */
  public function __construct($lhs, $rhs, $lhsProperty, $rhsColumn,
    $rhsProperty)
  {
    $this->_lhs = Parser::getModelInfo($lhs);
    $this->_rhs = Parser::getModelInfo($rhs);
    $this->_lhsProperty = $lhsProperty;
    $this->_rhsColumn = $rhsColumn;
    $this->_rhsProperty = $rhsProperty;
  }

  /**
   * Returns the code for deleting all of the owned right side entities that are
   * part of the relationship.
   */
  public function getDeleteCode() {
    $templateValues = Array
    (
      '${rhs}'          => $this->_rhs->getClass(),
      '${lhs_property}' => $this->_lhsProperty
    );

    // Use the instance cache since its likely that the template has already
    // been loaded for another relationship of the same type.
    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('one-to-many-delete', $templateValues);
    return $code;
  }

  /**
   * A one-to-many relationship does not store any information in the left hand
   * side so there is nothing to do here.
   */
  public function getLhsColumnName() {
    return null;
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
      '${rhs}'          => $this->_rhs->getClass(),
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
   * The left side doesn't contain any persisted information about the
   * relationship so there is nothing to do here.
   */
  public function getPopulateParameterCode() {
    return null;
  }

  /**
   * Returns the PHP code that either sets the id of the left side in the
   * right side or sets the left side itself in the right side.
   *
   * @return string PHP code that will create/update the entities on the right
   *   side of the relationship.
   */
  public function getSaveCode() {
    $templateValues = Array
    (
      '${rhs}'             => $this->_rhs->getClass(),
      '${lhs_property}'    => $this->_lhsProperty,
      '${rhs_id_property}' => $this->_rhs->getId()->getName(),
      '${rhs_property}'    => $this->_rhsProperty,
      '${rhs_column}'      => $this->_rhsColumn
    );

    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('one-to-many-save', $templateValues);
    return $code;
  }
}
