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

use \clarinet\model\ManyToOne;
use \reed\generator\CodeTemplateLoader;

/**
 * This class generates the persister code for a many-to-one relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/persister
 */
class ManyToOneBuilder implements RelationshipBuilderI {

  /* The relationship for which code is generated */
  private $_manyToOne;

  /**
   * Create a new relationship builder for a many-to-one relationship.
   *
   * @param ManyToOne $manyToOne
   */
  public function __construct(ManyToOne $manyToOne) {
    $this->_manyToOne = $manyToOne;
  }

  public function getDeleteCode() {
    return null;
  }

  public function getRetrieveCode() {
    $rhs = $this->_manyToOne->getRhs();
    $templateValues = Array
    (
      'rhs'             => $rhs->getClass(),
      'rhs_id_property' => $rhs->getId()->getName(),
      'lhs_property'    => $this->_manyToOne->getLhsProperty(),
      'lhs_column'      => $this->_manyToOne->getLhsColumn(),

      'rhs_str'         => str_replace('\\', '\\\\', $rhs->getClass())
    );

    $templateLoader = CodeTemplateLoader::get(__DIR__);
    $code = $templateLoader->load('many-to-one-retrieve', $templateValues);
    return $code;
  }

  public function getSaveRhsCode() {
    return null;
  }
}
