<?php

	namespace Inlm\Mappers;

	use LeanMapper;


	class UnderScoreMapper extends DefaultMapper
	{
		/**
		 * @inheritdoc
		 */
		public function getTable($entityClass)
		{
			return self::toUnderScore($this->trimNamespace($entityClass));
		}


		/**
		 * @inheritdoc
		 */
		public function getEntityClass($table, LeanMapper\Row $row = NULL)
		{
			return ($this->defaultEntityNamespace !== NULL ? $this->defaultEntityNamespace . '\\' : '')
				. ucfirst(self::toCamelCase($table));
		}


		/**
		 * @inheritdoc
		 */
		public function getColumn($entityClass, $field)
		{
			return self::toUnderScore($field);
		}


		/**
		 * @inheritdoc
		 */
		public function getEntityField($table, $column)
		{
			return self::toCamelCase($column);
		}


		/**
		 * @inheritdoc
		 */
		public function getTableByRepositoryClass($repositoryClass)
		{
			$matches = [];

			if (preg_match('#([a-z0-9]+)repository$#i', $repositoryClass, $matches)) {
				return self::toUnderScore($matches[1]);
			}

			throw new InvalidArgumentException('Cannot determine table name.');
		}


		/**
		 * @param  string
		 * @return string
		 */
		protected static function toUnderScore($s)
		{
			return lcfirst(preg_replace_callback('#(?<=.)([A-Z])#', function ($m) {
				return '_' . strtolower($m[1]);
			}, $s));
		}


		/**
		 * @param  string
		 * @return string
		 */
		protected static function toCamelCase($s)
		{
			return preg_replace_callback('#_(.)#', function ($m) {
				return strtoupper($m[1]);
			}, $s);
		}
	}
