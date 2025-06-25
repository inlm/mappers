<?php

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;

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
	$mapper = new Inlm\Mappers\DynamicMapper;

	Assert::same('article', $mapper->getTable(Model\Entity\Article::class));
	Assert::same(Model\Entity\Article::class, $mapper->getEntityClass('article'));
	Assert::same('article', $mapper->getTableByRepositoryClass(Model\ArticleRepository::class));
	Assert::same('id', $mapper->getPrimaryKey('article'));
});


// fallback + defaultEntityNamespace
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper(new Inlm\Mappers\DefaultMapper('App\Model'));

	Assert::same('article', $mapper->getTable(App\Model\Article::class));
	Assert::same(App\Model\Article::class, $mapper->getEntityClass('article'));
	Assert::same('article', $mapper->getTableByRepositoryClass(App\Model\ArticleRepository::class));
	Assert::same('id', $mapper->getPrimaryKey('article'));
});


// changed only primary key
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('article', NULL, NULL, 'article_id');

	Assert::same('article_id', $mapper->getPrimaryKey('article'));

	// fallback
	Assert::same('article', $mapper->getTable(Model\Entity\Article::class));
	Assert::same(Model\Entity\Article::class, $mapper->getEntityClass('article'));
	Assert::same('article', $mapper->getTableByRepositoryClass(Model\ArticleRepository::class));
});


// register only entity
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', Foo\Article::class);

	Assert::same('posts', $mapper->getTable(Foo\Article::class));
	Assert::same(Foo\Article::class, $mapper->getEntityClass('posts'));

	// fallback
	Assert::same('article', $mapper->getTableByRepositoryClass(Foo\ArticleRepository::class));
	Assert::same('id', $mapper->getPrimaryKey('posts'));
});


// register entity + repository
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', Foo\Article::class, Foo\ArticleRepository::class);

	Assert::same('posts', $mapper->getTable(Foo\Article::class));
	Assert::same(Foo\Article::class, $mapper->getEntityClass('posts'));
	Assert::same('posts', $mapper->getTableByRepositoryClass(Foo\ArticleRepository::class));

	// fallback
	Assert::same('id', $mapper->getPrimaryKey('posts'));
});


// register repository
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', NULL, Foo\ArticleRepository::class);

	Assert::same('posts', $mapper->getTableByRepositoryClass(Foo\ArticleRepository::class));

	// fallback
	Assert::same('article', $mapper->getTable(Foo\Article::class));
	Assert::same(Model\Entity\Posts::class, $mapper->getEntityClass('posts'));
	Assert::same(Model\Entity\Article::class, $mapper->getEntityClass('article'));
	Assert::same('id', $mapper->getPrimaryKey('posts'));
});


// register all
test(function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', Foo\Article::class, Foo\ArticleRepository::class, 'post_id');

	Assert::same('posts', $mapper->getTable(Foo\Article::class));
	Assert::same(Foo\Article::class, $mapper->getEntityClass('posts'));
	Assert::same('posts', $mapper->getTableByRepositoryClass(Foo\ArticleRepository::class));
	Assert::same('post_id', $mapper->getPrimaryKey('posts'));
});


// error - duplicated tableName
test (function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', Foo\Article::class, Foo\ArticleRepository::class);

	Assert::exception(function() use ($mapper) {
		$mapper->setMapping('posts', Bar\Article::class, Bar\ArticleRepository::class);
	}, Inlm\Mappers\DuplicateException::class, 'Table \'posts\' is already registered for entity Foo\Article');
});


// error - duplicated entity class
test (function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', Foo\Article::class, Foo\ArticleRepository::class);

	Assert::exception(function() use ($mapper) {
		$mapper->setMapping('news', Foo\Article::class, Bar\ArticleRepository::class);
	}, Inlm\Mappers\DuplicateException::class, 'Entity Foo\Article is already registered for table \'posts\'');
});


// error - duplicated repository class
test (function () {
	$mapper = new Inlm\Mappers\DynamicMapper;
	$mapper->setMapping('posts', Foo\Article::class, Foo\ArticleRepository::class);

	Assert::exception(function() use ($mapper) {
		$mapper->setMapping('news', Bar\Article::class, Foo\ArticleRepository::class);
	}, Inlm\Mappers\DuplicateException::class, 'Repository Foo\ArticleRepository is already registered for table \'posts\'');
});
