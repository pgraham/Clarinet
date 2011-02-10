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
 * This class encapsulates a Many-to-many relationship representation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/model
 */
class ManyToMany implements Relationship {

  private $_property;

  private $_linkTable;
  private $_linkLhsId;
  private $_linkRhsId;

  private $_rhs;
  private $_rhsIdColumn;
  private $_rhsIdProperty;

  private $_linkEntity = null;

  /**
   * Create a new Many-to-many relationship representation.  The left side of
   * the relationship is implied by the Info object to which the relationship
   * belongs.
   *
   * @param string $rhs The name of the entity on the right side of the
   *   relationship.
   * @param string $property The name of the property that contains the related
   *   entity.
   * @param string $rhsIdColumn The name of the right hand side entity's id
   *   column.
   * @param string $table The name of the table that contains the mapping.
   * @param string $localId The name of the column in the mapping table that
   *   contains the id of the entity on the left side of the relationship.
   * @param string $foreignId The name of the column in the mapping table that
   *   contains the id of the entity on the right side of the relationship.
   * @param string $linkEntity A special entity to use to represent the
   *   relationship.  This can be used to attach additional data to the mapping
   *   itself.  IMPORTANT:  The class that defines the link entity MUST extend
   *   the entity that form the right hand side of the relationship.
   */
  public function __construct($property, $rhs, $rhsIdColumn, $rhsIdProperty,
    $linkTable, $linkLhsId, $linkRhsId, $linkEntity = null)
  {
    $this->_property = $property;

    $this->_linkTable = $linkTable;
    $this->_linkLhsId = $linkLhsId;
    $this->_linkRhsId = $linkRhsId;

    $this->_rhs = $rhs;
    $this->_rhsIdColumn = $rhsIdColumn;
    $this->_rhsIdProperty = $rhsIdProperty;

    $this->_linkEntity = $linkEntity;
  }

  /**
   * Returns the code for populating the left hand side of the relationship with
   * the collection of entities from the 'many' side.
   *
   * @return string PHP code that will populate the model with the collection of
   *   related entities.
   */
  public function getPopulateCode() {
    if ($linkEntity === null) {
      return $this->_loadBasic();
    }  else {
      return $this->_loadLinkEntity();
    }
  }

  /*
   * Loads and populates the template for a many-to-many that does not involve a
   * special link entity.
   */
  private function _loadBasic() {
    $templateValues = Array
    (
      '${rel_property}'    => $this->_property,
      '${rhs}'             => $this->_rhs,
      '${rhs_id_column}'   => $this->_rhsId,
      
      '${link_table}'      => $this->_table,
      '${lhs_link_column}' => $this->_localId,
      '${rhs_link_column}' => $this->_foreignId
    );

    // Use the instance cache since its likely that the template has already
    // been loaded for another relationship of the same type.
    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-many', $templateValues);
    return $code;
  }

  /*
   * Loads and populates the template for a many-to-many that does involves a
   * special link entity.
   */
  private function loadLinkEntity() {
    $templateValues = Array
    (
    );

    // Use the instance cache since its likely that the template has already
    // been loaded for another relationship of the same type.
    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-many-link-entity', $templateValues);
    return $code;
  }
}
