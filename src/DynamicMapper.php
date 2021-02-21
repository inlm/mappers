<?php

	namespace Inlm\Mappers;

	use LeanMapper\Caller;
	use LeanMapper\IRowMapper;
	use LeanMapper\Row;


	class DynamicMapper implements IRowMapper
	{
		/** @var IRowMapper */
		protected $fallback;

		/** @var array  [tableName => entityClass] */
		protected $tableToEntity;

		/** @var array  [tableName => primaryKey] */
		protected $tablePrimaryKey;

		/** @var array  [entityClass => tableName] */
		protected $entityToTable;

		/** @var array  [repositoryClass => tableName] */
		protected $repositoryToTable;


		public function __construct(IRowMapper $fallback = NULL)
		{
			$this->fallback = $fallback ? $fallback : new \LeanMapper\DefaultMapper;
		}


		/**
		 * @param  string|NULL  table name in database
		 * @param  string|NULL
		 * @param  string|NULL
		 * @param  string|NULL
		 * @return static
		 */
		public function setMapping($tableName, $entityClass = NULL, $repositoryClass = NULL, $primaryKey = NULL)
		{
			if (isset($this->tableToEntity[$tableName])) {
				throw new DuplicateException("Table '$tableName' is already registered for entity " . $this->tableToEntity[$tableName]);
			}

			if (isset($entityClass, $this->entityToTable[$entityClass])) {
				throw new DuplicateException("Entity $entityClass is already registered for table '{$this->entityToTable[$entityClass]}'");
			}

			if (isset($repositoryClass, $this->repositoryToTable[$repositoryClass])) {
				throw new DuplicateException("Repository $repositoryClass is already registered for table '{$this->repositoryToTable[$repositoryClass]}'");
			}

			if (is_string($entityClass)) {
				$this->tableToEntity[$tableName] = $entityClass;
				$this->entityToTable[$entityClass] = $tableName;
			}

			if (is_string($repositoryClass)) {
				$this->repositoryToTable[$repositoryClass] = $tableName;
			}

			if (is_string($primaryKey)) {
				$this->tablePrimaryKey[$tableName] = $primaryKey;
			}

			return $this;
		}


		public function getPrimaryKey($table)
		{
			if (isset($this->tablePrimaryKey[$table])) {
				return $this->tablePrimaryKey[$table];
			}

			return $this->fallback->getPrimaryKey($table);
		}


		public function getTable($entityClass)
		{
			if (isset($this->entityToTable[$entityClass])) {
				return $this->entityToTable[$entityClass];
			}

			return $this->fallback->getTable($entityClass);
		}


		public function getEntityClass($table, Row $row = NULL)
		{
			if (isset($this->tableToEntity[$table])) {
				return $this->tableToEntity[$table];
			}

			return $this->fallback->getEntityClass($table, $row);
		}


		public function getColumn($entityClass, $field)
		{
			return $this->fallback->getColumn($entityClass, $field);
		}


		public function getEntityField($table, $column)
		{
			return $this->fallback->getEntityField($table, $column);
		}


		public function getRelationshipTable($sourceTable, $targetTable)
		{
			return $this->fallback->getRelationshipTable($sourceTable, $targetTable);
		}


		public function getRelationshipColumn($sourceTable, $targetTable, $relationshipName = NULL)
		{
			return $this->fallback->getRelationshipColumn($sourceTable, $targetTable, $relationshipName);
		}


		public function getTableByRepositoryClass($repositoryClass)
		{
			if (isset($this->repositoryToTable[$repositoryClass])) {
				return $this->repositoryToTable[$repositoryClass];
			}

			return $this->fallback->getTableByRepositoryClass($repositoryClass);
		}


		public function getImplicitFilters($entityClass, Caller $caller = null)
		{
			return $this->fallback->getImplicitFilters($entityClass, $caller);
		}


		public function convertToRowData($table, array $values)
		{
			return $this->fallback->convertToRowData($table, $values);
		}


		public function convertFromRowData($table, array $data)
		{
			return $this->fallback->convertFromRowData($table, $data);
		}
	}
