<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test(function () {
	$mapper = new Inlm\Mappers\PrefixMapper('prefix_', new Inlm\Mappers\CamelCaseMapper('App\Entity'));

	Assert::same('id', $mapper->getPrimaryKey('orderItem'));
	Assert::same('prefix_orderItem', $mapper->getTable('OrderItem'));
	Assert::same('App\Entity\OrderItem', $mapper->getEntityClass('prefix_orderItem', NULL));
	Assert::same('customerName', $mapper->getColumn('OrderItem', 'customerName'));
	Assert::same('customerName', $mapper->getEntityField('prefix_orderItem', 'customerName'));
	Assert::same('prefix_invoice_orderItem', $mapper->getRelationshipTable('prefix_invoice', 'prefix_orderItem'));
	Assert::same('orderItem_id', $mapper->getRelationshipColumn('prefix_order', 'prefix_orderItem'));
	Assert::same('prefix_orderItem', $mapper->getTableByRepositoryClass('OrderItemRepository'));
	Assert::same(array(), $mapper->getImplicitFilters('OrderItem', NULL));
});


test(function () {
	$mapper = new Inlm\Mappers\PrefixMapper(NULL);
	Assert::same('orderitem', $mapper->getTable('OrderItem'));
});
