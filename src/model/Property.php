<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\orm\model;

use Exception;

/**
 * This object encapsulates information about a model property.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Property {

	const TYPE_BOOLEAN   = 'boolean';
	const TYPE_DATE      = 'date';
	const TYPE_DECIMAL   = 'decimal';
	const TYPE_EMAIL     = 'email';
	const TYPE_FLOAT     = 'float';
	const TYPE_INTEGER   = 'integer';
	const TYPE_STRING    = 'string';
	const TYPE_TEXT      = 'text';
	const TYPE_TIMESTAMP = 'timestamp';

	public static $ALL_TYPES = array(
		self::TYPE_BOOLEAN,
		self::TYPE_DATE,
		self::TYPE_DECIMAL,
		self::TYPE_EMAIL,
		self::TYPE_FLOAT,
		self::TYPE_INTEGER,
		self::TYPE_STRING,
		self::TYPE_TEXT,
		self::TYPE_TIMESTAMP
	);

	private $column;
	private $default;
	private $model;
	private $name;
	private $notNull;
	private $type;
	private $values;

	/**
	 * Create a new persisted property representation.
	 *
	 * @param string $name
	 *   The name of the property.
	 * @param string $column
	 *   The name of the column in the database table in which instances are
	 *   persisted.
	 */
	public function __construct($name, $column) {
		$this->name = $name;
		$this->column = $column;

		// Default type is string
		$this->type = self::TYPE_STRING;

		// Default is to allow null values
		$this->notNull = false;
	}

	/**
	 * Getter for the name of the column in which the property's values are
	 * stored.
	 *
	 * @return string
	 */
	public function getColumn() {
		return $this->column;
	}

	/**
	 * Getter for the default value for the property.  If no default is specified
	 * then the default is null.
	 *
	 * @return mixed
	 */
	public function getDefault() {
		return $this->default;
	}

	/**
	 * Getter for the model to which the property belongs.
	 *
	 * @return Model
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * Getter for the name of the property.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Getter for the type of the property.
	 *
	 * @return string One of this class's TYPE_ constants
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Getter for the set of possible values for the property.
	 *
	 * @return array
	 */
	public function getValues() {
		return $this->values;
	}

	/**
	 * Flag for whether or not the set of values for the property is restricted by
	 * an enumeration.
	 *
	 * @return boolean
	 */
	public function isEnumerated() {
		return $this->values !== null;
	}

	/**
	 * Getter/setter for whether or not the property is allowed to contain null
	 * values.
	 *
	 * @param boolean $notNull If specified, then the method acts as a setter.
	 * @return boolean
	 */
	public function notNull($notNull = null) {
		if (is_bool($notNull)) {
			$this->notNull = $notNull;
		}
		return $this->notNull;
	}

	/**
	 * Setter for the default value of the property.
	 *
	 * @param string $default The default value for the property.
	 */
	public function setDefault($default) {
		$this->default = $default;
	}

	/**
	 * Setter for the model that this property belongs to.
	 *
	 * @param Model $model
	 */
	public function setModel(Model $model) {
		$this->model = $model;
	}

	/**
	 * Setter for the type of the property.
	 *
	 * @param string $type One of this class's TYPE_ constants
	 */
	public function setType($type) {
		$type = strtolower($type);
		if (!in_array($type, self::$ALL_TYPES)) {
			throw new Exception("Unsupported property type: $type");
		}
		$this->type = $type;
	}

	/**
	 * Setter for the set of accepted values for the property.
	 *
	 * @param array $values
	 */
	public function setValues(array $values) {
		$this->values = $values;
	}
}
