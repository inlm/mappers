<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Website
{
	private $url;


	public function __construct($url)
	{
		$this->url = $url;
	}


	public function getUrl()
	{
		return $this->url;
	}
}


class Price
{
	private $price;
	private $currency;


	public function __construct($price, $currency)
	{
		$this->price = $price;
		$this->currency = $currency;
	}


	public function getPrice()
	{
		return $this->price;
	}


	public function getCurrency()
	{
		return $this->currency;
	}
}


test(function () {
	$mapper = new Inlm\Mappers\RowMapper;

	Assert::same('id', $mapper->getPrimaryKey('orderitem'));
	Assert::same('orderitem', $mapper->getTable(OrderItem::class));
	Assert::same(Model\Entity\Orderitem::class, $mapper->getEntityClass('orderitem', NULL));
	Assert::same('customerName', $mapper->getColumn(OrderItem::class, 'customerName'));
	Assert::same('customerName', $mapper->getEntityField('orderitem', 'customerName'));
	Assert::same('invoice_orderitem', $mapper->getRelationshipTable('invoice', 'orderitem'));
	Assert::same('orderitem_id', $mapper->getRelationshipColumn('order', 'orderitem'));
	Assert::same('orderitem', $mapper->getTableByRepositoryClass(OrderItemRepository::class));
	Assert::same([], $mapper->getImplicitFilters(OrderItem::class, NULL));
});


// mapping
test(function () {
	$mapper = new Inlm\Mappers\RowMapper;
	$mapper->registerFieldMapping(
		Model\Client::class,
		'website',
		function ($dbValue) {
			return new Website($dbValue);
		},
		function (Website $rowValue) {
			return $rowValue->getUrl();
		}
	);

	$dbData = [
		'id' => 1,
		'website' => 'http://example.com',
		'name' => 'Client ABC',
	];

	$rowData = $mapper->convertToRowData('client', $dbData);
	Assert::same($dbData, $mapper->convertFromRowData('client', $rowData));

	$dbData = [
		'id' => 2,
		'website' => NULL,
		'name' => 'Client XYZ',
	];

	$rowData = $mapper->convertToRowData('client', $dbData);
	Assert::same($dbData, $mapper->convertFromRowData('client', $rowData));
});


// mapping - only from DB
test(function () {
	$mapper = new Inlm\Mappers\RowMapper;
	$mapper->registerFieldMapping(
		Model\Client::class,
		'name',
		function ($dbValue) {
			return strtoupper($dbValue);
		}
	);

	$rowData = $mapper->convertToRowData('client', [
		'id' => 1,
		'name' => 'Client ABC',
	]);
	Assert::same('CLIENT ABC', $rowData['name']);
	Assert::same([
		'id' => 1,
		'name' => 'CLIENT ABC',
	], $mapper->convertFromRowData('client', $rowData));
});


// mapping - only to DB
test(function () {
	$mapper = new Inlm\Mappers\RowMapper;
	$mapper->registerFieldMapping(
		Model\Client::class,
		'name',
		NULL,
		function ($dbValue) {
			return strtolower($dbValue);
		}
	);

	$rowData = $mapper->convertToRowData('client', [
		'id' => 1,
		'name' => 'Client ABC',
	]);
	Assert::same('Client ABC', $rowData['name']);
	Assert::same([
		'id' => 1,
		'name' => 'client abc',
	], $mapper->convertFromRowData('client', $rowData));
});


// multi column mapping
test(function () {
	$mapper = new Inlm\Mappers\RowMapper;
	$mapper->registerFieldMapping(
		Model\OrderItem::class,
		'price_currency',
		function ($value) {
			return strtoupper($value);
		},
		function ($value) {
			return strtolower($value);
		}
	);
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

	$dbData = [
		'id' => 1,
		'price_total' => 1234,
		'price_currency' => 'eur',
	];

	$rowData = $mapper->convertToRowData('orderitem', $dbData);
	Assert::same('EUR', $rowData['price_currency']);
	Assert::same($dbData, $mapper->convertFromRowData('orderitem', $rowData));
});



// error - duplicated mapping
test(function () {
	$mapper = new Inlm\Mappers\RowMapper;
	$mapper->registerFieldMapping(Model\Entity\Client::class, 'website', function () {}, function () {});

	Assert::exception(function() use ($mapper) {
		$mapper->registerFieldMapping(App\Entity\Client::class, 'website', function () {}, function () {});
	}, Inlm\Mappers\DuplicateException::class, "Convertor for table 'client' and column 'website' (field App\Entity\Client::\$website) already exists.");
});


// error - missing convertors
test(function () {
	$mapper = new Inlm\Mappers\RowMapper;
	Assert::exception(function() use ($mapper) {
		$mapper->registerFieldMapping(Model\Entity\Client::class, 'website', NULL, NULL);
	}, Inlm\Mappers\InvalidArgumentException::class, "Missing convertors for Model\Entity\Client::\$website, both are NULL.");
});
