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
 */
namespace clarinet\transformer;

use \clarinet\model\Relationship;

use \reed\generator\CodeTemplateLoader;

/**
 * This class generates the PHP code for relationship model <=> array
 * transformations.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class RelationshipBuilder {

  private $_templateLoader;

  public function __construct() {
    $this->_templateLoader = CodeTemplateLoader::get(__DIR__);
  }

  public function buildToArray(Relationship $relationship) {
    $templateName = $this->_getTemplateName($relationship);

    $templateValues = array(
      'relationship'  => $relationship->getLhsProperty(),
      'rhsIdProperty' => $relationship->getRhs()->getId()->getName()
    );

    return $this->_templateLoader->load("$templateName-to-array.php",
      $templateValues);
  }

  public function buildFromArray(Relationship $relationship) {
    $templateName = $this->_getTemplateName($relationship);

    $templateValues = array(
      'relationship'  => $relationship->getLhsProperty(),
      'rhs'           => $relationship->getRhs()->getClass(),
      'rhsIdProperty' => $relationship->getRhs()->getId()->getName()
    );

    return $this->_templateLoader->load("$templateName-from-array.php",
      $templateValues);
  }

  private function _getTemplateName($relationship) {
    $type = get_class($relationship);
    switch ($type) {

      case 'clarinet\model\OneToMany':
      return 'one-to-many';

      case 'clarinet\model\ManyToOne':
      return 'many-to-one';

      case 'clarinet\model\ManyToMany':
      return 'many-to-many';

      default:
      assert("false /* Unrecognized relationship type: $type");
    }
  }
}
