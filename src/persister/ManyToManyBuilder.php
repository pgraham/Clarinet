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
 * @package clarinet/persister
 */
namespace clarinet\persister;

use \clarinet\model\ManyToMany;
use \clarinet\TemplateLoader;

/**
 * This class generates the persister code for a many-to-many relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/persister
 */
class ManyToManyBuilder implements RelationshipBuilderI {

  /* The relationship for which code is generated */
  private $_manyToMany;

  /**
   * Create a new relationship builder for a many-to-many relationship.
   *
   * @param ManyToMany $manyToMany
   */
  public function __construct(ManyToMany $manyToMany) {
    $this->_manyToMany = $manyToMany;
  }

  public function getDeleteCode() {
    $templateValues = Array
    (
      '${rhs}'             => $this->_manyToMany->getRhs()->getClass(),
      '${link_table}'      => $this->_manyToMany->getLinkTable(),
      '${lhs_link_column}' => $this->_manyToMany->getLinkLhsId(),
    );

    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-many-delete', $templateValues);
    return $code;
  }

  public function getRetrieveCode() {
    $rhs = $this->_manyToMany->getRhs();
    $templateValues = Array
    (
      '${rhs}'             => $rhs->getClass(),
      '${rhs_table}'       => $rhs->getTable(),
      '${rhs_id_column}'   => $rhs->getId()->getColumn(),
      
      '${link_table}'      => $this->_manyToMany->getLinkTable(),
      '${lhs_link_column}' => $this->_manyToMany->getLinkLhsId(),
      '${rhs_link_column}' => $this->_manyToMany->getLinkRhsId(),

      '${lhs_property}'    => $this->_manyToMany->getLhsProperty()
    );

    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-many-retrieve', $templateValues);
    return $code;
  }

  public function getSaveLhsCode() {
    return null;
  }

  public function getSaveRhsCode() {
    $rhs = $this->_manyToMany->getRhs();
    $templateValues = Array
    (
      '${rhs}'             => $rhs->getClass(),
      '${rhs_id_property}' => $rhs->getId()->getName(),
      
      '${link_table}'      => $this->_manyToMany->getLinkTable(),
      '${lhs_link_column}' => $this->_manyToMany->getLinkLhsId(),
      '${rhs_link_column}' => $this->_manyToMany->getLinkRhsId(),

      '${lhs_property}'    => $this->_manyToMany->getLhsProperty()
    );

    $templateLoader = TemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-many-save', $templateValues);
    return $code;
  }
}
