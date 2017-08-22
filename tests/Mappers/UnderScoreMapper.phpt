<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test(function () {
	$mapper = new Inlm\Mappers\UnderScoreMapper('App\Entity');

	Assert::same('id', $mapper->getPrimaryKey('order_item'));
	Assert::same('order_item', $mapper->getTable('OrderItem'));
	Assert::same('App\Entity\OrderItem', $mapper->getEntityClass('order_item', NULL));
	Assert::same('customer_name', $mapper->getColumn('OrderItem', 'customerName'));
	Assert::same('customerName', $mapper->getEntityField('order_item', 'customer_name'));
	Assert::same('invoice_order_item', $mapper->getRelationshipTable('invoice', 'order_item'));
	Assert::same('order_item_id', $mapper->getRelationshipColumn('order', 'order_item'));
	Assert::same('order_item', $mapper->getTableByRepositoryClass('OrderItemRepository'));
	Assert::same(array(), $mapper->getImplicitFilters('OrderItem', NULL));
});
