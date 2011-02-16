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
 * This class encapsulates a many-to-one relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class ManyToOne extends AbstractRelationship {

  private $_column;

  /**
   * Creates a new Many-to-one relationship representation.
   *
   * @param string $lhs The name of the entity on the left side of the
   *   relationship.
   * @param string $rhs The name of the entity on the right side of the
   *   relationship.
   * @param string $property The name of the model's property that contains the
   *   relationship.
   * @param string $column The name of the model table's column that contains
   *   the id of the related entity.
   */
  public function __construct($lhs, $rhs, $property, $column) {
    parent::__construct($lhs, $rhs, $property);
    $this->_column = $column;
  }

  /**
   * Returns the name of the column that represents the relationship in the
   * database
   */
  public function getLhsColumnName() {
    return $this->_column;
  }

  /**
   * Generates the PHP code that will populate a variable name $model, which is
   * an instance of the relationship's left hand side, with the model from the
   * right hand side of the relationship
   *
   * @return string PHP Code that will populate the model on the left hand side
   *   of the relationship with the model from the right hand side.
   */
  public function getPopulateModelCode() {
    $templateValues = $this->_getTemplateValues();

    // Use the instance cache since its likely that the template has already
    // been loaded for another relationship of the same type.
    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-one-model', $templateValues);
    return $code;
  }

  /**
   * Generates the PHP code that will populate an array named $param with a
   * key value pair appropriate for use with INSERT and UPDATE statements.
   */
  public function getPopulateParameterCode() {
    $templateValues = $this->_getTemplateValues();

    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-one-param', $templateValues);
    return $code;
  }

  /* Create an array of template values for the relationship's templates. */
  private function _getTemplateValues() {
    return Array
    (
      '${rhs}'             => $this->_rhs->getClass(),
      '${rhs_id_property}' => $this->_rhs->getId()->getName(),
      '${lhs_property}'    => $this->_lhsProperty,
      '${lhs_column}'      => $this->_column
    );
  }
}
