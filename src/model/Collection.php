<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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
namespace zpt\orm\model;

/**
 * This class encapsulates the base functionality for collection mappings.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
abstract class Collection
{

    /** The type of collection. */
    protected $type;

    /* The name of the property that holds the collection in the model. */
    protected $property;

    /* The name of the database table that persists the collection. */
    protected $linkTable;

    /* The name of the column that links items to a specific entity. */
    protected $idColumn;

    /* The name of the column that holds an item's value. */
    protected $valueColumn;

    /**
     * Create a new collection mapping.
     *
     * @param string $type        The type of collection
     * @param string $property    The name of the property that holds the
     *                            collection.
     * @param string $linkTable   The name of the table that stores the
     *                            collection.
     * @param string $idColumn    The name of the column that links items to an
     *                            entity.
     * @param string $valueColumn The name of the column that holds an item's
     *                            value.
     */
    public function __construct(
        $type,
        $property,
        $linkTable,
        $idColumn,
        $valueColumn
    ) {
        $this->type = $type;
        $this->property = $property;
        $this->linkTable = $linkTable;
        $this->idColumn = $idColumn;
        $this->valueColumn = $valueColumn;
    }

    /**
     * Create an array representation of the collection suitable for resolving
     * templates.
     *
     * @return array
     */
    public function asArray() {
      return array(
          'type'     => $this->type,
          'property' => $this->property,
          'link'     => $this->linkTable,
          'idCol'    => $this->idColumn,
          'valCol'   => $this->valueColumn
      );
    }

    /**
     * Return the name of this collection.
     */
    public function getName() {
      return $this->linkTable;
    }

}
