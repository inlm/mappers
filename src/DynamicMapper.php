<?php

	declare(strict_types=1);

	namespace Inlm\Mappers;

	use LeanMapper\Caller;
	use LeanMapper\IMapper;
	use LeanMapper\Row;


	class DynamicMapper implements IMapper
	{
		/** @var IMapper */
		protected $fallback;

		/** @var array<string, string>  [tableName => entityClass] */
		protected $tableToEntity;

		/** @var array<string, string>  [tableName => primaryKey] */
		protected $tablePrimaryKey;

		/** @var array<string, string>  [entityClass => tableName] */
		protected $entityToTable;

		/** @var array<string, string>  [repositoryClass => tableName] */
		protected $repositoryToTable;


		public function __construct(IMapper $fallback = NULL)
		{
			$this->fallback = $fallback ? $fallback : new \LeanMapper\DefaultMapper;
		}


		public function setMapping(string $tableName, ?string $entityClass = NULL, ?string $repositoryClass = NULL, ?string $primaryKey = NULL): self
		{
			if (isset($this->tableToEntity[$tableName])) {
				throw new DuplicateException("Table '$tableName' is already registered for entity " . $this->tableToEntity[$tableName]);
			}

			if ($entityClass !== NULL && isset($this->entityToTable[$entityClass])) {
				throw new DuplicateException("Entity $entityClass is already registered for table '{$this->entityToTable[$entityClass]}'");
			}

			if ($repositoryClass !== NULL && isset($this->repositoryToTable[$repositoryClass])) {
				throw new DuplicateException("Repository $repositoryClass is already registered for table '{$this->repositoryToTable[$repositoryClass]}'");
			}

			if (is_string($entityClass)) {
				$this->tableToEntity[$tableName] = $entityClass;
				$this->entityToTable[$entityClass] = $tableName;
			}

			if ($repositoryClass !== NULL) {
				$this->repositoryToTable[$repositoryClass] = $tableName;
			}

			if (is_string($primaryKey)) {
				$this->tablePrimaryKey[$tableName] = $primaryKey;
			}

			return $this;
		}


		public function getPrimaryKey(string $table): string
		{
			if (isset($this->tablePrimaryKey[$table])) {
				return $this->tablePrimaryKey[$table];
			}

			return $this->fallback->getPrimaryKey($table);
		}


		public function getTable(string $entityClass): string
		{
			if (isset($this->entityToTable[$entityClass])) {
				return $this->entityToTable[$entityClass];
			}

			return $this->fallback->getTable($entityClass);
		}


		public function getEntityClass(string $table, ?Row $row = NULL): string
		{
			if (isset($this->tableToEntity[$table])) {
				return $this->tableToEntity[$table];
			}

			return $this->fallback->getEntityClass($table, $row);
		}


		public function getColumn(string $entityClass, string $field): string
		{
			return $this->fallback->getColumn($entityClass, $field);
		}


		public function getEntityField(string $table, string $column): string
		{
			return $this->fallback->getEntityField($table, $column);
		}


		public function getRelationshipTable(string $sourceTable, string $targetTable): string
		{
			return $this->fallback->getRelationshipTable($sourceTable, $targetTable);
		}


		public function getRelationshipColumn(string $sourceTable, string $targetTable, ?string $relationshipName = NULL): string
		{
			return $this->fallback->getRelationshipColumn($sourceTable, $targetTable, $relationshipName);
		}


		public function getTableByRepositoryClass(string $repositoryClass): string
		{
			if (isset($this->repositoryToTable[$repositoryClass])) {
				return $this->repositoryToTable[$repositoryClass];
			}

			return $this->fallback->getTableByRepositoryClass($repositoryClass);
		}


		public function getImplicitFilters(string $entityClass, ?Caller $caller = NULL)
		{
			return $this->fallback->getImplicitFilters($entityClass, $caller);
		}


		public function convertToRowData(string $table, array $values): array
		{
			return $this->fallback->convertToRowData($table, $values);
		}


		public function convertFromRowData(string $table, array $data): array
		{
			return $this->fallback->convertFromRowData($table, $data);
		}
	}
