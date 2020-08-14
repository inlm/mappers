<?php

	namespace Inlm\Mappers;

	use LeanMapper\Caller;
	use LeanMapper\IMapper;
	use LeanMapper\Row;


	class PrefixMapper implements IMapper
	{
		/** @var string */
		protected $prefix;

		/** @var IMapper */
		protected $fallback;

		/** @var int */
		protected $prefixLength;


		/**
		 * @param  string|NULL
		 */
		public function __construct($prefix = '', IMapper $fallback = NULL)
		{
			$this->prefix = (string) $prefix;
			$this->prefixLength = strlen($prefix);
			$this->fallback = $fallback ? $fallback : new \LeanMapper\DefaultMapper;
		}


		public function getPrimaryKey($table)
		{
			return $this->fallback->getPrimaryKey($this->removePrefix($table));
		}


		public function getTable($entityClass)
		{
			return $this->prefix . $this->fallback->getTable($entityClass);
		}


		public function getEntityClass($table, Row $row = NULL)
		{
			return $this->fallback->getEntityClass($this->removePrefix($table), $row);
		}


		public function getColumn($entityClass, $field)
		{
			return $this->fallback->getColumn($entityClass, $field);
		}


		public function getEntityField($table, $column)
		{
			return $this->fallback->getEntityField($this->removePrefix($table), $column);
		}


		public function getRelationshipTable($sourceTable, $targetTable)
		{
			return $this->prefix . $this->fallback->getRelationshipTable($this->removePrefix($sourceTable), $this->removePrefix($targetTable));
		}


		public function getRelationshipColumn($sourceTable, $targetTable)
		{
			return $this->fallback->getRelationshipColumn($this->removePrefix($sourceTable), $this->removePrefix($targetTable));
		}


		public function getTableByRepositoryClass($repositoryClass)
		{
			return $this->prefix . $this->fallback->getTableByRepositoryClass($repositoryClass);
		}


		public function getImplicitFilters($entityClass, Caller $caller = null)
		{
			return $this->fallback->getImplicitFilters($entityClass, $caller);
		}


		/**
		 * @param  string
		 * @return string
		 */
		protected function removePrefix($table)
		{
			if ($this->prefix !== '' && strncmp($this->prefix, $table, $this->prefixLength) === 0) {
				return substr($table, $this->prefixLength);
			}

			return $table;
		}
	}
