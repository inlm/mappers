# Inlm\Mappers

[![Build Status](https://github.com/inlm/mappers/workflows/Build/badge.svg)](https://github.com/inlm/mappers/actions)
[![Downloads this Month](https://img.shields.io/packagist/dm/inlm/mappers.svg)](https://packagist.org/packages/inlm/mappers)
[![Latest Stable Version](https://poser.pugx.org/inlm/mappers/v/stable)](https://github.com/inlm/mappers/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/inlm/mappers/blob/master/license.md)

Mappers for Lean Mapper.

<a href="https://www.janpecha.cz/donate/"><img src="https://buymecoffee.intm.org/img/donate-banner.v1.svg" alt="Donate" height="100"></a>


Installation
------------

[Download a latest package](https://github.com/inlm/mappers/releases) or use [Composer](http://getcomposer.org/):

```
composer require inlm/mappers
```

`Inlm\Mappers` requires PHP 8.0 or later.


## Usage

| Mapper                          | Entity      | Table        |  Column         | Note
|---------------------------------|-------------|--------------|-----------------|------------------
| `Inlm\Mappers\DefaultMapper`    | `OrderItem` | `orderitem`  | `customerName`  | *only extends `LeanMapper\DefaultMapper`*
| `Inlm\Mappers\CamelCaseMapper`  | `OrderItem` | `orderItem`  | `customerName`  | There is [issue](https://dev.mysql.com/doc/refman/5.5/en/identifier-case-sensitivity.html) for MySQL on OS Windows.
| `Inlm\Mappers\UnderScoreMapper` | `OrderItem` | `order_item` | `customer_name` |
| `Inlm\Mappers\DynamicMapper`    | ~           | ~            | ~               | See below.
| `Inlm\Mappers\PrefixMapper`     | ~           | ~            | ~               | See below.
| `Inlm\Mappers\RowMapper`        | ~           | ~            | ~               | See below.
| `Inlm\Mappers\StiMapper`        | ~           | ~            | ~               | See below.


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


### RowMapper

RowMapper maps values to / from `LeanMapper\Row` (requires Lean Mapper 3.5+).

``` php
$mapper = new RowMapper;
$mapper = new RowMapper($fallbackMapper);
$mapper->registerFieldMapping($entity, $field, $fromDbValue, $toDbValue);
$mapper->registerFieldMapping(
	Model\Entity\Client::class,
	'website',
	function ($dbValue) {
		return new Website($dbValue);
	},
	function (Website $rowValue) {
		return $rowValue->getUrl();
	}
);


// multi column mapping
$mapper->registerMultiValueMapping(
	Model\OrderItem::class,
	'price',
	function (array $values, $rowField) {
		return new Price($values[$rowField . '_total'], $values[$rowField . '_currency']);
	},
	function (Price $price, $rowField) {
		return [
			$rowField . '_total' => $price->getPrice(),
			$rowField . '_currency' => $price->getCurrency(),
		];
	}
);
```


### StiMapper

StiMapper simplifies working with Single Table Inheritance.

``` php
$mapper = new StiMapper;
$mapper = new StiMapper($fallbackMapper);
```

Registration of STI types:

``` php
$mapper->registerStiType($baseEntity, $typeValue, $entityClass);
$mapper->registerStiType(Entities\Client::class, 'company', Entities\ClientCompany::class);
$mapper->registerStiType(Entities\Client::class, 'individual', Entities\ClientIndividual::class);
```

Default STI type column is named `type`, you can change it with:

``` php
$mapper->registerTypeField(Entities\Client::class, 'clientType');
```

You can limit `LeanMapper\Fluent` for specific STI type:

``` php
$fluent = $connection->select('*')->from('client');
$mapper->applyStiMapping($fluent, Entities\ClientCompany::class);
echo $fluent; // SELECT * FROM `client` WHERE `client`.`clientType` = 'company'
```

### How change default entity namespace

``` php
$mapper = new Inlm\Mappers\DefaultMapper('App\Entity');
$mapper = new Inlm\Mappers\CamelCaseMapper('App\Entity');
$mapper = new Inlm\Mappers\UnderScoreMapper('App\Entity');
```


### Recommended order of mappers

* `RowMapper`
* `StiMapper`
* `PrefixMapper`
* `DynamicMapper`
* `DefaultMapper` / `CamelCaseMapper` / `UnderScoreMapper`


------------------------------

License: [New BSD License](license.md)
<br>Author: Jan Pecha, https://www.janpecha.cz/
