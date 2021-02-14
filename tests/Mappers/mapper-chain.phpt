<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$underscoreMapper = new Inlm\Mappers\UnderScoreMapper('Foo\\Entities');

$dynamicMapper = new Inlm\Mappers\DynamicMapper($underscoreMapper);
$dynamicMapper->setMapping(
	'orderItems',
	Foo\Entities\OrderItem::class,
	Foo\Repositories\OrderItemRepository::class,
	'item_id'
);

$prefixMapper = new Inlm\Mappers\PrefixMapper('prefix_', $dynamicMapper);

$stiMapper = new Inlm\Mappers\StiMapper($prefixMapper);
$stiMapper->registerTypeField(Foo\Entities\Client::class, 'clientType');
$stiMapper->registerStiType(Foo\Entities\Client::class, 'company', Foo\Entities\ClientCompany::class);

$mapper = $stiMapper;


test(function () use ($mapper) {
	Assert::same('id', $mapper->getPrimaryKey('prefix_user_role'));
	Assert::same('item_id', $mapper->getPrimaryKey('prefix_orderItems'));
	Assert::same('id', $mapper->getPrimaryKey('prefix_client'));

	Assert::same('prefix_user_role', $mapper->getTable(Foo\Entities\UserRole::class));
	Assert::same('prefix_orderItems', $mapper->getTable(Foo\Entities\OrderItem::class));
	Assert::same('prefix_client', $mapper->getTable(Foo\Entities\Client::class));
	Assert::same('prefix_client', $mapper->getTable(Foo\Entities\ClientCompany::class));

	Assert::same(Foo\Entities\UserRole::class, $mapper->getEntityClass('prefix_user_role', NULL));
	Assert::same(Foo\Entities\OrderItem::class, $mapper->getEntityClass('prefix_orderItems', NULL));
	Assert::same(Foo\Entities\Client::class, $mapper->getEntityClass('prefix_client', NULL));
	Assert::same(Foo\Entities\ClientCompany::class, $mapper->getEntityClass('prefix_client', createRow([
		'id' => 1,
		'client_type' => 'company'
	])));

	Assert::same('role_name', $mapper->getColumn(Foo\Entities\UserRole::class, 'roleName'));
	Assert::same('customer_name', $mapper->getColumn(Foo\Entities\OrderItem::class, 'customerName'));
	Assert::same('client_type', $mapper->getColumn(Foo\Entities\Client::class, 'clientType'));

	Assert::same('roleName', $mapper->getEntityField('prefix_user_role', 'role_name'));
	Assert::same('customerName', $mapper->getEntityField('prefix_orderItems', 'customer_name'));
	Assert::same('clientType', $mapper->getEntityField('prefix_client', 'client_type'));

	Assert::same('prefix_client_orderItems', $mapper->getRelationshipTable('prefix_client', 'prefix_orderItems'));

	Assert::same('orderItems_id', $mapper->getRelationshipColumn('prefix_client', 'prefix_orderItems'));

	Assert::same('prefix_user_role', $mapper->getTableByRepositoryClass(Foo\Repositories\UserRoleRepository::class));
	Assert::same('prefix_orderItems', $mapper->getTableByRepositoryClass(Foo\Repositories\OrderItemRepository::class));
	Assert::same('prefix_client', $mapper->getTableByRepositoryClass(Foo\Repositories\ClientRepository::class));
});


test(function () use ($stiMapper) {
	$fluent = createFluent('prefix_client');
	$stiMapper->applyStiMapping($fluent, Foo\Entities\ClientCompany::class);
	Assert::same('SELECT * FROM [prefix_client] WHERE [prefix_client].[client_type] = \'company\'', (string) $fluent);
});