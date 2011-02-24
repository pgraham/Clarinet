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

use \clarinet\model\OneToMany;
use \reed\generator\CodeTemplateLoader;

/**
 * This class generates the persister code for a one-to-many relationship.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet/persister
 */
class OneToManyBuilder implements RelationshipBuilderI {

  /* The relationship for which code is generated */
  private $_oneToMany;

  /**
   * Create a new relationship builder for a one-to-many relationship.
   *
   * @param OneToMany $oneToMany
   */
  public function __construct(OneToMany $oneToMany) {
    $this->_oneToMany = $oneToMany;
  }

  public function getDeleteCode() {
    $templateValues = Array
    (
      'rhs'          => $this->_oneToMany->getRhs()->getClass(),
      'lhs_property' => $this->_oneToMany->getLhsProperty()
    );

    $templateLoader = CodeTemplateLoader::get(__DIR__);
    $code = $templateLoader->load('one-to-many-delete', $templateValues);
    return $code;
  }

  public function getRetrieveCode() {
    $templateValues = Array
    (
      'rhs'          => $this->_oneToMany->getRhs()->getClass(),
      'rhs_column'   => $this->_oneToMany->getRhsColumn(),
      'lhs_property' => $this->_oneToMany->getLhsProperty()
    );

    $templateLoader = CodeTemplateLoader::get(__DIR__);
    $code = $templateLoader->load('one-to-many-retrieve', $templateValues);
    return $code;
  }

  public function getSaveLhsCode() {
    return null;
  }

  public function getSaveRhsCode() {
    $rhs = $this->_oneToMany->getRhs();
    $lhs = $this->_oneToMany->getLhs();


    // Determine if this is a mirrored relationship and output the appropriate
    // code
    if ($rhs->hasRelationship('many-to-one', $lhs->getClass())) {
      // Get the name of the property that contains the mirrored relationship
      $mirrorRel = $rhs->getRelationship('many-to-one', $lhs->getClass());
      $mirrorProp = $mirrorRel->getLhsProperty();
      $mirrorCol = $mirrorRel->getLhsColumn();

      $templateValues = Array
      (
        'rhs'             => $rhs->getClass(),
        'lhs_property'    => $this->_oneToMany->getLhsProperty(),
        'rhs_id_property' => $rhs->getId()->getName(),
        'rhs_property'    => $mirrorProp,
        'rhs_column'      => $mirrorCol
      );
      $templateName = 'one-to-many-save-mirror';
    } else {
      $templateValues = Array
      (
        'rhs'             => $rhs->getClass(),
        'lhs_property'    => $this->_oneToMany->getLhsProperty(),
        'rhs_id_property' => $rhs->getId()->getName(),
        'rhs_property'    => $this->_oneToMany->getRhsProperty(),
        'rhs_column'      => $this->_oneToMany->getRhsColumn()
      );
      $templateName = 'one-to-many-save';
    }

    $templateLoader = CodeTemplateLoader::get(__DIR__);
    $code = $templateLoader->load($templateName, $templateValues);
    return $code;
  }
}
