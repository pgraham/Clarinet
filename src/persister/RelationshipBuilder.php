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

use \clarinet\Exception;
use \clarinet\model\Relationship;

/**
 * This class generates persister code for a relationship.  This class acts
 * as a common interface for relationship persistance code generation.  Actual
 * generation is delegated to another class based on the type of relationship
 * used to construct.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/persister
 */
class RelationshipBuilder implements RelationshipBuilderI {

  /* The relationship for which code is generated */
  private $_relationship;

  /*
   * The RelationshipBuilderI implementation to which code generation is
   * delegated.
   */
  private $_builder;


  /**
   * Create a new relationship builder for the given relationship.
   *
   * @param Relationship $relationship
   */
  public function __construct(Relationship $relationship) {
    $this->_relationship = $relationship;

    $type = get_class($relationship);
    switch ($type) {

      case 'clarinet\model\OneToMany':
      $this->_builder = new OneToManyBuilder($relationship);
      break;

      case 'clarinet\model\ManyToOne':
      $this->_builder = new ManyToOneBuilder($relationship);
      break;

      case 'clarinet\model\ManyToMany':
      $this->_builder = new ManyToManyBuilder($relationship);
      break;

      default:
      throw new Exception("Unrecognized relationship type: $type");
    }
  }

  /**
   * Delegates to the relationship building for the type of relationship given
   * to the constructor.
   */
  public function getDeleteCode() {
    return $this->_builder->getDeleteCode();
  }

  /**
   * Delegates to the relationship building for the type of relationship given
   * to the constructor.
   */
  public function getRetrieveCode() {
    return $this->_builder->getRetrieveCode();
  }

  /**
   * Delegates to the relationship building for the type of relationship given
   * to the constructor.
   */
  public function getSaveLhsCode() {
    return $this->_builder->getSaveLhsCode();
  }

  /**
   * Delegates to the relationship building for the type of relationship given
   * to the constructor.
   */
  public function getSaveRhsCode() {
    return $this->_builder->getSaveRhsCode();
  }
}
