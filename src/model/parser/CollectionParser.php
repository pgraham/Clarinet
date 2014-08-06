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
namespace zpt\orm\model\parser;

use \zpt\orm\model\ListCollection;
use \zpt\orm\model\MapCollection;
use \zpt\orm\model\SetCollection;
use \ReflectionMethod;

/**
 * This class is responsible for parsing collection mappings for a model class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class CollectionParser extends BaseMethodParser
{

    public function parse($class)
    {
        $methods = $class->getMethods();

        $collections = array();
        foreach ($methods as $method) {
            $annos = $this->annotationFactory->get($method);

            if (isset($annos['set'])) {
                $collections[] = $this->parseSet($method, $annos);

            } else if (isset($annos['list'])) {
                $collections[] = $this->parseList($method, $annos);

            } else if (isset($annos['map'])) {
                $collections[] = $this->parseMap($method, $annos);
            }
        }
        return $collections;
    }

    public function parseSet(ReflectionMethod $method, $annos)
    {
        $property = $this->getPropertyName($method);
        $anno = $annos['set'];

        $linkTable = $this->getLinkTable($method, $anno, $property);
        $idColumn = $this->getIdColumn($method, $anno, $property);
        $valueColumn = $this->getValueColumn($method, $anno, $property);

        $collection = new SetCollection(
            $property,
            $linkTable,
            $idColumn,
            $valueColumn
        );
        return $collection;

    }

    public function parseList(ReflectionMethod $method, $annos)
    {
        $property = $this->getPropertyName($method);
        $anno = $annos['list'];

        $linkTable = $this->getLinkTable($method, $anno, $property);
        $idColumn = $this->getIdColumn($method, $anno, $property);
        $valueColumn = $this->getValueColumn($method, $anno, $property);

        $seqColumn = $anno['sequenceColumn'];
        if ($seqColumn === null) {
            // TODO Should this be delegated to the naming strategy?
            $seqColumn = 'seq';
        }

        $collection = new ListCollection(
            $property,
            $linkTable,
            $idColumn,
            $valueColumn,
            $seqColumn
        );
        return $collection;
    }

    public function parseMap(ReflectionMethod $method, $annos)
    {
        $property = $this->getPropertyName($method);
        $anno = $annos['map'];

        $linkTable = $this->getLinkTable($method, $anno, $property);
        $idColumn = $this->getIdColumn($method, $anno, $property);
        $valueColumn = $this->getValueColumn($method, $anno, $property);

        $keyColumn = $anno['keyColumn'];
        if ($keyColumn === null) {
            // TODO Should this be delegated to the naming strategy?
            $keyColumn = 'key';
        }

        $collection = new MapCollection(
            $property,
            $linkTable,
            $idColumn,
            $valueColumn,
            $keyColumn
        );
        return $collection;
    }

    private function getIdColumn($method, $anno, $property)
    {
        $idColumn = $anno['idColumn'];
        if ($idColumn === null) {
            $lhs = $this->modelCache->get(
                $method->getDeclaringClass()->getName()
            );
            $idColumn = $this->namingStrategy->getLinkColumn($lhs);
        }
        return $idColumn;
    }

    private function getLinkTable($method, $anno, $property)
    {
        $linkTable = $anno['table'];
        if ($linkTable === null) {
            $lhs = $this->modelCache->get(
                $method->getDeclaringClass()->getName()
            );
            
            $linkTable = $this->namingStrategy->getCollectionLink(
                $lhs,
                $property
            );
        }
        return $linkTable;
    }

    private function getValueColumn($method, $anno, $property) {
        $valueColumn = $anno['valueColumn'];
        if ($valueColumn === null) {
            // TODO Should this be delegated to the naming strategy?
            $valueColumn = 'val';
        }
        return $valueColumn;
    }
}
