<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test(function () {
	$mapper = new Inlm\Mappers\CamelCaseMapper('App\Entity');

	Assert::same('id', $mapper->getPrimaryKey('orderItem'));
	Assert::same('orderItem', $mapper->getTable('OrderItem'));
	Assert::same('App\Entity\OrderItem', $mapper->getEntityClass('orderItem', NULL));
	Assert::same('customerName', $mapper->getColumn('OrderItem', 'customerName'));
	Assert::same('customerName', $mapper->getEntityField('orderItem', 'customerName'));
	Assert::same('invoice_orderItem', $mapper->getRelationshipTable('invoice', 'orderItem'));
	Assert::same('orderItem_id', $mapper->getRelationshipColumn('order', 'orderItem'));
	Assert::same('orderItem', $mapper->getTableByRepositoryClass('OrderItemRepository'));
	Assert::same([], $mapper->getImplicitFilters('OrderItem', NULL));
});
