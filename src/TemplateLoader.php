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
 * @package clarinet
 */
namespace clarinet;

/**
 * This class loads a PHP template into which it substitutes given values.
 *
 * @author Philip Graham
 * @package clarinet
 */
class TemplateLoader {

  /* The base path for where templates are located. */
  private $_basePath;

  /* Cache of previously loaded templates */
  private $_cache = Array();

  /**
   * Create a new template loader for loading templates located in the directory
   * specified by the given path.
   *
   * @param string $basePath Path to the directory where template are located.
   */
  public function __construct($basePath) {
    $this->_basePath = $basePath;
  }

  /**
   * Loads the specified template into which it substitutes the given values.
   *
   * @param string $templateName
   * @param array $templateValues
   */
  public function load($templateName, Array $templateValues) {
    if (!isset($this->_cache[$templateName])) {
      $templatePath = $this->_basePath . "/$templateName.template";
      $this->_cache[$templateName] = file_get_contents($templatePath);
    }

    $template = $this->_cache[$templateName];

    $toReplace   = array_keys($templateValues);
    $replaceWith = array_values($templateValues);

    $body = str_replace($toReplace, $replaceWith, $template);
    return $body;
  }
}
