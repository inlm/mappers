<?php

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();


function test(callable $cb): void
{
	$cb();
}


/**
 * @param  array<string, mixed> $data
 */
function createRow(array $data): ?LeanMapper\Row
{
	$result = LeanMapper\Result::createDetachedInstance();
	$row = $result->getRow();

	foreach ($data as $k => $v) {
		$row->$k = $v;
	}

	return $row;
}


function createFluent(string $tableName): Dibi\Fluent
{
	$fluent = new LeanMapper\Fluent(new LeanMapper\Connection(['driver' => 'sqlite3', 'file' => ':memory:', 'lazy' => TRUE]));
	$fluent->select('*')->from($tableName);
	return $fluent;
}
