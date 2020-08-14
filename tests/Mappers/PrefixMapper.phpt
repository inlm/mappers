<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test(function () {
	$mapper = new Inlm\Mappers\PrefixMapper('prefix_', new Inlm\Mappers\CamelCaseMapper('App\Entity'));

	Assert::same('id', $mapper->getPrimaryKey('orderItem'));
	Assert::same('prefix_orderItem', $mapper->getTable(OrderItem::class));
	Assert::same(App\Entity\OrderItem::class, $mapper->getEntityClass('prefix_orderItem', NULL));
	Assert::same('customerName', $mapper->getColumn(OrderItem::class, 'customerName'));
	Assert::same('customerName', $mapper->getEntityField('prefix_orderItem', 'customerName'));
	Assert::same('prefix_invoice_orderItem', $mapper->getRelationshipTable('prefix_invoice', 'prefix_orderItem'));
	Assert::same('orderItem_id', $mapper->getRelationshipColumn('prefix_order', 'prefix_orderItem'));
	Assert::same('prefix_orderItem', $mapper->getTableByRepositoryClass(OrderItemRepository::class));
	Assert::same([], $mapper->getImplicitFilters(OrderItem::class, NULL));
});


test(function () {
	$mapper = new Inlm\Mappers\PrefixMapper(NULL);
	Assert::same('orderitem', $mapper->getTable(OrderItem::class));
});
