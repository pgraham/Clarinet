# ORMdr

Clarinet is an ORM for PHP with a syntax loosely based on JPA/Hibernate.
However, Clarinet is nowhere near as feature rich as Hibernate. It is intended
to be easy and fast to build with. This makes it ideal for small websites and
prototyping.

Throughout this guide and the code, you will see the terms model and entity.
Model is used to refer to the definition of something that can be persisted
while entity is used to refer to an instance of a model.

* * *
NOTE: Although the documentation is being updated to more consistent in its use
of these terms, there may be some spots where they are used interchangeably.
* * *

## Install

Composer: zeptech/clarinet

## Model Classes

Persistance information is extracted from model classes. Typically, each table
in your database will be represented by a model class.  An exception being link
tables for a many-to-many relationship, which are handled transparently by
Clarinet.

* * *
All annotations used for defining model classes are parsed according to the
rules of the
[php-annotations library](https://github.com/pgraham/php-annotations).
* * *

A model class must follow some simple conventions that make them look a lot like
Java beans.

Each model class must annotated with @Entity:

```php
<?php
/** @Entity(table = simple_entity) */
class SimpleEntity {
  // ...
}
```

For now the entity annotation must define a 'table' parameter which contains the
name of the database table in which entities are persisted.  This will be
changed in the future so that if the table name is not provided a sensible
default will be chosen.  Most likely the name of the class with camel-casing
switched to lowercase with underscores between the words.

### Id Properties

Each model class must define an id column.  An id column is defined through a
getter/setter pair similar to the following:

```php
<?php
class SimpleEntity {

  private $id;

  /** @Id */
  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }
}
```
* * *
NOTE: Clarinet will fail if a getXXX method is annotated by something
      understood by clarinet but does not have a matching setXXX method.
* * *

The previous code will assume that the column in the database table that
contains the id is named 'id'. To specify a different column you can add a
@Column annotation:

```php
<?php
  // ...

  /**
   * @Id
   * @Column(name = id)
   */
```

### Column Properties

The @Column annotation is also used to denote the getter/setter pairs for the
other columns in the table that are handled by Clarinet.  So if the
simple_entity table had a column 'name', you could define a getter/setter pair
something like this:

```php
<?php
class SimpleEntity {

  private $id;
  private $name;

  // Id getter/setter ...

  /** @Column(name = name) */
  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }
}
```

The name parameter to the column annotation is optional.  If it is not present
the name of the column is assumed to be a lowercased version of the XXX part of
getXXX().  So the previous sample could be written as:

```php
<?php
  // ...

  /**
   * @Column
   */
  public function getName() {
    return $this->name;
  }

```

Other supported annotations for columns:

  - _@Type_: The type of data contained by the property. In addition to standard
    database type, Clarinet provides support for some types (such as email)
    which provide an addtional abstraction to standard database
    types. These types are enforced at runtime by a Validator actor.
  - _@Enumerated_: If a column only supports a finite list of values, those
    values can be defined using the values parameter of an _@enumerated_
    annotation, e.g. `@enumerated( values = { ... } )`.

### Relationships

Clarinet also supports relationship definition using annotated getter/setter
pairs. There are three types of supported relationships, many-to-one,
one-to-many and many-to-many. Relationships are specified with a getter which
is annotated with either @ManyToOne, @OneToMany or @ManyToMany. As with id and
column getter, they must be accompanied by a setter. Each of the relationship
annotations require an 'entity' parameter which is the name of the
entity on the right side of the relationship. All relationships have a left side
and a right.  The left side is the entity which declares relationship.
Relationships which are declared on both sides are called *mirrored*.

```php
<?php
  // ...

  /** @OneToMany( entity = Category ) */
  public function getCategories() {
    return $this->categories;
  }
```

#### One-to-many relationships

One to many relationships result in an array of related models being populated
in the model.  This done by joining a column in the table of the related model
to the id of the entity.  The column can be specified using the 'column'
parameter of the @OneToMany annotation.  The column for the SimpleEntity example
above would be 'simple_entity_id'. To specify the column do the following:

```php
<?php

  // ...

  /**
   * @OneToMany( entity = Category, column = simple_entity_id )
   */
  public function getCategories() {
    return $this->_categories;
  }
```

**NOTE** For uni-directional one-to-many mappings, one of two conditions must
hold in order to avoid errors. Either the *one* side ID column in the *many*
table must be nullable or the the id must be populated in the *many* entity
programatically before saving. This is because without a bi-directional
many-to-one mapping, there is no way for the ORM to know which property in the
many side maps to the one side ID.

#### Ordering

For one-to-many and many-to-many relationships, the order in which the related
entities are returned is undefined by default. If the order is important an
order parameter can be specified in the annotation. The value of the parameter
is the name of the column.  The direction can be specified with the 'dir'
parameter.  The default direction is ASC.

```php
<?php

  // ...

  /** @OneToMany( entity = Category, order = name, dir = asc ) */
  public function getCategories() {
    return $this->_categories;
  }
```

* * *

Clarinet is licensed under the 3-clause BSD license, the text of which can be
found in the file LICENSE.txt in the same directory as this file.

### Collections

Clarinet supports collection relationships for storing array properties. There
are three supported collection types, @Set, @List and @Map

## Usage

Clarinet uses the
[PHP Code Templates](https://github.com/pgraham/php-code-templates) library
for generating persister, validator and transformer actors for a given model
class. In order to use clarinet\*Generator classes, the classes for this library
must be available.

### Initialization

TODO

### Generating actors

TODO

### Actors

#### Instantiation

TODO

#### Usage

TODO
