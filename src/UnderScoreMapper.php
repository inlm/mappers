<?php

	namespace Inlm\Mappers;

	use LeanMapper;


	class UnderScoreMapper extends DefaultMapper
	{
		public function getTable(string $entityClass): string
		{
			return self::toUnderScore(\LeanMapper\Helpers::trimNamespace($entityClass));
		}


		public function getEntityClass(string $table, ?LeanMapper\Row $row = null): string
		{
			return ($this->defaultEntityNamespace !== NULL ? $this->defaultEntityNamespace . '\\' : '')
				. ucfirst(self::toCamelCase($table));
		}


		public function getColumn(string $entityClass, string $field): string
		{
			return self::toUnderScore($field);
		}


		public function getEntityField(string $table, string $column): string
		{
			return self::toCamelCase($column);
		}


		public function getTableByRepositoryClass(string $repositoryClass): string
		{
			$matches = [];

			if (preg_match('#([a-z0-9]+)repository$#i', $repositoryClass, $matches)) {
				return self::toUnderScore($matches[1]);
			}

			throw new InvalidArgumentException('Cannot determine table name.');
		}


		protected static function toUnderScore(string $s): string
		{
			return lcfirst(preg_replace_callback('#(?<=.)([A-Z])#', function ($m) {
				return '_' . strtolower($m[1]);
			}, $s));
		}


		protected static function toCamelCase(string $s): string
		{
			return preg_replace_callback('#_(.)#', function ($m) {
				return strtoupper($m[1]);
			}, $s);
		}
	}
