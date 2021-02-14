<?php

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();


function test($cb)
{
	$cb();
}


function createRow(array $data)
{
	$result = LeanMapper\Result::createDetachedInstance();
	$row = $result->getRow();

	foreach ($data as $k => $v) {
		$row->$k = $v;
	}

	return $row;
}


function createFluent($tableName)
{
	$fluent = new LeanMapper\Fluent(new LeanMapper\Connection(['driver' => 'sqlite3', 'lazy' => TRUE]));
	$fluent->select('*')->from($tableName);
	return $fluent;
}
