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
 * This class encapsulates a Many-to-many relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class ManyToMany extends AbstractRelationship {

  private $_linkTable;
  private $_linkLhsId;
  private $_linkRhsId;

  /**
   * Create a new Many-to-many relationship representation.  The left side of
   * the relationship is implied by the Info object to which the relationship
   * belongs.
   *
   * @param string $lhs The name of the entity on the left side of the
   *   relationship.
   * @param string $rhs The name of the entity on the right side of the
   *   relationship.
   * @param string $property The name of the property that contains the related
   *   entity.
   * @param string $linkTable The name of the table that contains the mapping.
   * @param string $linkLhsId The name of the column in the mapping table that
   *   contains the id of the entity on the left side of the relationship.
   * @param string $linkRhsId The name of the column in the mapping table that
   *   contains the id of the entity on the right side of the relationship.
   */
  public function __construct($lhs, $rhs, $property, $linkTable, $linkLhsId,
    $linkRhsId)
  {
    parent::__construct($lhs, $rhs, $property);
    $this->_linkTable = $linkTable;
    $this->_linkLhsId = $linkLhsId;
    $this->_linkRhsId = $linkRhsId;
  }

  /**
   * Generates and returns the code for deleting the left side of the
   * relationship.
   */
  public function getDeleteCode() {
    $templateValues = Array
    (
      '${rhs}'             => $this->_rhs->getClass(),
      '${link_table}'      => $this->_linkTable,
      '${lhs_link_column}' => $this->_linkLhsId,
    );

    // Use the instance cache since its likely that the template has already
    // been loaded for another relationship of the same type.
    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-many-delete', $templateValues);
    return $code;
  }

  /**
   * Returns the code for populating the left hand side of the relationship with
   * the collection of entities from the 'many' side.
   *
   * @return string PHP code that will populate the model with the collection of
   *   related entities.
   */
  public function getPopulateModelCode() {
    $templateValues = Array
    (
      '${rhs}'             => $this->_rhs->getClass(),
      '${rhs_table}'       => $this->_rhs->getTable(),
      '${rhs_id_column}'   => $this->_rhs->getId()->getColumn(),
      
      '${link_table}'      => $this->_linkTable,
      '${lhs_link_column}' => $this->_linkLhsId,
      '${rhs_link_column}' => $this->_linkRhsId,

      '${lhs_property}'    => $this->_lhsProperty
    );

    // Use the instance cache since its likely that the template has already
    // been loaded for another relationship of the same type.
    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-many-model', $templateValues);
    return $code;
  }

  /**
   * Return the PHP code that creates the relationship.
   */
  public function getSaveCode() {
    $templateValues = Array
    (
      '${rhs}'             => $this->_rhs->getClass(),
      '${rhs_id_property}' => $this->_rhs->getId()->getName(),
      
      '${link_table}'      => $this->_linkTable,
      '${lhs_link_column}' => $this->_linkLhsId,
      '${rhs_link_column}' => $this->_linkRhsId,

      '${lhs_property}'    => $this->_lhsProperty
    );

    // Use the instance cache since its likely that the template has already
    // been loaded for another relationship of the same type.
    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-many-save', $templateValues);
    return $code;
  }
}
