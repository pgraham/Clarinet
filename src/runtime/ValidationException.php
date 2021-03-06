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
namespace zpt\orm\runtime;

use \Exception;

/**
 * This class encapsulates an exception that occured while validating an entity.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ValidationException extends Exception
{

    private $modelClass;
    private $msgs;

    public function __construct(array $msgs, $modelClass)
    {
        parent::__construct();
        $this->msgs = $msgs;
        $this->modelClass = $modelClass;
    }

    public function getMessages()
    {
        return $this->msgs;
    }

    public function getModelClass()
    {
        return $this->modelClass;
    }
}
