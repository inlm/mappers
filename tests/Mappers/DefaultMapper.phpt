<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test(function () {
	$mapper = new Inlm\Mappers\DefaultMapper('App\Entity');

	Assert::same('id', $mapper->getPrimaryKey('orderitem'));
	Assert::same('orderitem', $mapper->getTable('OrderItem'));
	Assert::same('App\Entity\Orderitem', $mapper->getEntityClass('orderitem', NULL));
	Assert::same('customerName', $mapper->getColumn('OrderItem', 'customerName'));
	Assert::same('customerName', $mapper->getEntityField('orderitem', 'customerName'));
	Assert::same('invoice_orderitem', $mapper->getRelationshipTable('invoice', 'orderitem'));
	Assert::same('orderitem_id', $mapper->getRelationshipColumn('order', 'orderitem'));
	Assert::same('orderitem', $mapper->getTableByRepositoryClass('OrderItemRepository'));
	Assert::same(array(), $mapper->getImplicitFilters('OrderItem', NULL));
});
