<?php

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;

	Assert::same('id', $mapper->getPrimaryKey('orderitem'));
	Assert::same('orderitem', $mapper->getTable('OrderItem'));
	Assert::same('Model\Entity\Orderitem', $mapper->getEntityClass('orderitem', NULL));
	Assert::same('customerName', $mapper->getColumn('OrderItem', 'customerName'));
	Assert::same('customerName', $mapper->getEntityField('orderitem', 'customerName'));
	Assert::same('invoice_orderitem', $mapper->getRelationshipTable('invoice', 'orderitem'));
	Assert::same('orderitem_id', $mapper->getRelationshipColumn('order', 'orderitem'));
	Assert::same('orderitem', $mapper->getTableByRepositoryClass('OrderItemRepository'));
	Assert::same(array(), $mapper->getImplicitFilters('OrderItem', NULL));
});


// fallback
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;

	Assert::same('article', $mapper->getTable('Model\Entity\Article'));
	Assert::same('Model\Entity\Article', $mapper->getEntityClass('article'));
	Assert::same('article', $mapper->getTableByRepositoryClass('Model\ArticleRepository'));
	Assert::same('id', $mapper->getPrimaryKey('article'));
});


// fallback + defaultEntityNamespace
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper(new Inlm\Mappers\DefaultMapper('App\Model'));

	Assert::same('article', $mapper->getTable('App\Model\Article'));
	Assert::same('App\Model\Article', $mapper->getEntityClass('article'));
	Assert::same('article', $mapper->getTableByRepositoryClass('App\Model\ArticleRepository'));
	Assert::same('id', $mapper->getPrimaryKey('article'));
});


// changed only primary key
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('article', NULL, NULL, 'article_id');

	Assert::same('article_id', $mapper->getPrimaryKey('article'));

	// fallback
	Assert::same('article', $mapper->getTable('Model\Entity\Article'));
	Assert::same('Model\Entity\Article', $mapper->getEntityClass('article'));
	Assert::same('article', $mapper->getTableByRepositoryClass('Model\ArticleRepository'));
});


// register only entity
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', 'Foo\Article');

	Assert::same('posts', $mapper->getTable('Foo\Article'));
	Assert::same('Foo\Article', $mapper->getEntityClass('posts'));

	// fallback
	Assert::same('article', $mapper->getTableByRepositoryClass('Foo\ArticleRepository'));
	Assert::same('id', $mapper->getPrimaryKey('posts'));
});


// register entity + repository
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', 'Foo\Article', 'Foo\ArticleRepository');

	Assert::same('posts', $mapper->getTable('Foo\Article'));
	Assert::same('Foo\Article', $mapper->getEntityClass('posts'));
	Assert::same('posts', $mapper->getTableByRepositoryClass('Foo\ArticleRepository'));

	// fallback
	Assert::same('id', $mapper->getPrimaryKey('posts'));
});


// register repository
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', NULL, 'Foo\ArticleRepository');

	Assert::same('posts', $mapper->getTableByRepositoryClass('Foo\ArticleRepository'));

	// fallback
	Assert::same('article', $mapper->getTable('Foo\Article'));
	Assert::same('Model\Entity\Posts', $mapper->getEntityClass('posts'));
	Assert::same('Model\Entity\Article', $mapper->getEntityClass('article'));
	Assert::same('id', $mapper->getPrimaryKey('posts'));
});


// register all
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', 'Foo\Article', 'Foo\ArticleRepository', 'post_id');

	Assert::same('posts', $mapper->getTable('Foo\Article'));
	Assert::same('Foo\Article', $mapper->getEntityClass('posts'));
	Assert::same('posts', $mapper->getTableByRepositoryClass('Foo\ArticleRepository'));
	Assert::same('post_id', $mapper->getPrimaryKey('posts'));
});


// error - duplicated tableName
test (function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', 'Foo\Article', 'Foo\ArticleRepository');

	Assert::exception(function() use ($mapper) {
		$mapper->setMapping('posts', 'Bar\Article', 'Bar\ArticleRepository');
	}, 'Inlm\Mappers\DuplicateException', 'Table \'posts\' is already registered for entity Foo\Article');
});


// error - duplicated entity class
test (function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', 'Foo\Article', 'Foo\ArticleRepository');

	Assert::exception(function() use ($mapper) {
		$mapper->setMapping('news', 'Foo\Article', 'Bar\ArticleRepository');
	}, 'Inlm\Mappers\DuplicateException', 'Entity Foo\Article is already registered for table \'posts\'');
});


// error - duplicated repository class
test (function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', 'Foo\Article', 'Foo\ArticleRepository');

	Assert::exception(function() use ($mapper) {
		$mapper->setMapping('news', 'Bar\Article', 'Foo\ArticleRepository');
	}, 'Inlm\Mappers\DuplicateException', 'Repository Foo\ArticleRepository is already registered for table \'posts\'');
});