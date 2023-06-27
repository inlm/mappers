<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test(function () {
	$mapper = new Inlm\Mappers\StiMapper;

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


// fallback
test(function () {
	$mapper = new Inlm\Mappers\StiMapper;

	Assert::same('article', $mapper->getTable(Model\Entity\Article::class));
	Assert::same(Model\Entity\Article::class, $mapper->getEntityClass('article'));
	Assert::same('article', $mapper->getTableByRepositoryClass(Model\ArticleRepository::class));
	Assert::same('id', $mapper->getPrimaryKey('article'));
});


// type field
test(function () {
	$mapper = new Inlm\Mappers\StiMapper;
	$mapper->registerTypeField(Model\Entity\Article::class, 'type2');
	$mapper->registerTypeField(Model\Entity\Article::class, 'type');
});


// mapping
test(function () {
	$mapper = new Inlm\Mappers\StiMapper;
	$mapper->registerStiType(Model\Entity\Client::class, 'individual', Model\Entity\ClientIndividual::class);
	$mapper->registerStiType(Model\Entity\Client::class, 'company', Model\Entity\ClientCompany::class);

	Assert::same(Model\Entity\Client::class, $mapper->getEntityClass('client', NULL));
	Assert::same('client', $mapper->getTable(Model\Entity\Client::class));

	$result = LeanMapper\Result::createDetachedInstance();
	$row = $result->getRow();
	$row->type = 'individual';
	Assert::same(Model\Entity\ClientIndividual::class, $mapper->getEntityClass('client', $row));
	Assert::same('client', $mapper->getTable(Model\Entity\ClientIndividual::class));

	$mapper->registerTypeField(Model\Entity\Client::class, 'clientType');
	$result = LeanMapper\Result::createDetachedInstance();
	$row = $result->getRow();
	$row->clientType = 'company';
	Assert::same(Model\Entity\ClientCompany::class, $mapper->getEntityClass('client', $row));
	Assert::same('client', $mapper->getTable(Model\Entity\ClientCompany::class));

	Assert::exception(function() use ($mapper) {
		$result = LeanMapper\Result::createDetachedInstance();
		$row = $result->getRow();
		$row->clientType = 'unknowType';
		$mapper->getEntityClass('client', $row);
	}, Inlm\Mappers\InvalidStateException::class, 'Unknow type \'unknowType\' for base entity Model\Entity\Client.');
});


// fluent mapping
test(function () {
	$mapper = new Inlm\Mappers\StiMapper;
	$mapper->registerStiType(Model\Entity\Client::class, 'individual', Model\Entity\ClientIndividual::class);
	$mapper->registerStiType(Model\Entity\Client::class, 'company', Model\Entity\ClientCompany::class);
	$mapper->registerTypeField(Model\Entity\Client::class, 'clientType');

	$fluent = new LeanMapper\Fluent(new LeanMapper\Connection(['driver' => 'sqlite3', 'file' => ':memory:', 'lazy' => TRUE]));
	$fluent->select('*')->from('client');
	$mapper->applyStiMapping($fluent, Model\Entity\ClientCompany::class);

	Assert::same('SELECT * FROM [client] WHERE [client].[clientType] = \'company\'', (string) $fluent);
});


// error - duplicated entity class
test(function () {
	$mapper = new Inlm\Mappers\StiMapper;
	$mapper->registerStiType(Model\Entity\Client::class, 'individual', Model\Entity\ClientIndividual::class);

	Assert::exception(function() use ($mapper) {
		$mapper->registerStiType(Model\Entity\Client::class, 'individual', Model\Entity\ClientIndividual2::class);
	}, Inlm\Mappers\DuplicateException::class, 'Type \'individual\' for entity Model\Entity\Client already exists.');

	Assert::exception(function() use ($mapper) {
		$mapper->registerStiType(Model\Entity\BaseClient::class, 'individual', Model\Entity\ClientIndividual::class);
	}, Inlm\Mappers\DuplicateException::class, 'Entity Model\Entity\ClientIndividual is already registered.');
});
