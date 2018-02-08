
# Inlm\Mappers

[![Build Status](https://travis-ci.org/inlm/mappers.svg?branch=master)](https://travis-ci.org/inlm/mappers)

Mappers for Lean Mapper.

<a href="https://www.patreon.com/bePatron?u=9680759"><img src="https://c5.patreon.com/external/logo/become_a_patron_button.png" alt="Become a Patron!" height="35"></a>


Installation
------------

[Download a latest package](https://github.com/inlm/mappers/releases) or use [Composer](http://getcomposer.org/):

```
composer require inlm/mappers
```

`Inlm\Mappers` requires PHP 5.4.0 or later.


## Usage

| Mapper                          | Entity      | Table        |  Column         | Note
|---------------------------------|-------------|--------------|-----------------|------------------
| `Inlm\Mappers\DefaultMapper`    | `OrderItem` | `orderitem`  | `customerName`  | *only extends `LeanMapper\DefaultMapper`*
| `Inlm\Mappers\CamelCaseMapper`  | `OrderItem` | `orderItem`  | `customerName`  | There is [issue](https://dev.mysql.com/doc/refman/5.5/en/identifier-case-sensitivity.html) for MySQL on OS Windows.
| `Inlm\Mappers\UnderScoreMapper` | `OrderItem` | `order_item` | `customer_name` |
| `Inlm\Mappers\DynamicMapper`    | ~           | ~            | ~               | See below.
| `Inlm\Mappers\PrefixMapper`     | ~           | ~            | ~               | See below.


### DynamicMapper

Dynamic mapper uses explicit mapping of entities and tables.

``` php
$mapper = new DynamicMapper;
$mapper->setMapping(
	'order_items', // table name - required
	'OrderItem', // entity class - optional
	'OrderItemRepository', // repository class - optional
	'item_id' // primary key - optional
);
```

If there's no mapping for entity or table, call is passed to fallback mapper (`LeanMapper\DefaultMapper` by default):

``` php
$mapper = new DynamicMapper;
$mapper->getTable('OrderItem'); // returns 'orderitem'

$mapper = new DynamicMapper(new Inlm\Mappers\UnderScoreMapper);
$mapper->getTable('OrderItem'); // returns 'order_item'
```


### PrefixMapper

PrefixMapper adds & removes prefix from table names.

``` php
$mapper = new PrefixMapper('prefix_');
$mapper = new PrefixMapper('prefix_', $fallbackMapper);
```

PrefixMapper only processes prefixes in table names, everything else is given to fallback mapper (`LeanMapper\DefaultMapper` by default):

``` php
$mapper = new PrefixMapper('prefix_');
echo $mapper->getTable('OrderItem'); // prints 'prefix_orderitem'

$mapper = new PrefixMapper('prefix_', new Inlm\Mappers\UnderScoreMapper);
echo $mapper->getTable('OrderItem'); // prints 'prefix_order_item'
```


### How change default entity namespace

``` php
$mapper = new Inlm\Mappers\DefaultMapper('App\Entity');
$mapper = new Inlm\Mappers\CamelCaseMapper('App\Entity');
$mapper = new Inlm\Mappers\UnderScoreMapper('App\Entity');
```


------------------------------

License: [New BSD License](license.md)
<br>Author: Jan Pecha, https://www.janpecha.cz/
