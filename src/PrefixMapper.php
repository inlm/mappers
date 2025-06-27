<?php

	declare(strict_types=1);

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


		public function __construct(?string $prefix = '', ?IMapper $fallback = NULL)
		{
			$this->prefix = (string) $prefix;
			$this->prefixLength = strlen($this->prefix);
			$this->fallback = $fallback ? $fallback : new \LeanMapper\DefaultMapper;
		}


		public function getPrimaryKey(string $table): string
		{
			return $this->fallback->getPrimaryKey($this->removePrefix($table));
		}


		public function getTable(string $entityClass): string
		{
			return $this->prefix . $this->fallback->getTable($entityClass);
		}


		public function getEntityClass(string $table, ?Row $row = null): string
		{
			return $this->fallback->getEntityClass($this->removePrefix($table), $row);
		}


		public function getColumn(string $entityClass, string $field): string
		{
			return $this->fallback->getColumn($entityClass, $field);
		}


		public function getEntityField(string $table, string $column): string
		{
			return $this->fallback->getEntityField($this->removePrefix($table), $column);
		}


		public function getRelationshipTable(string $sourceTable, string $targetTable): string
		{
			return $this->prefix . $this->fallback->getRelationshipTable($this->removePrefix($sourceTable), $this->removePrefix($targetTable));
		}


		public function getRelationshipColumn(string $sourceTable, string $targetTable, ?string $relationshipName = NULL): string
		{
			return $this->fallback->getRelationshipColumn($this->removePrefix($sourceTable), $this->removePrefix($targetTable), $relationshipName);
		}


		public function getTableByRepositoryClass(string $repositoryClass): string
		{
			return $this->prefix . $this->fallback->getTableByRepositoryClass($repositoryClass);
		}


		public function getImplicitFilters(string $entityClass, ?Caller $caller = NULL)
		{
			return $this->fallback->getImplicitFilters($entityClass, $caller);
		}


		public function convertToRowData(string $table, array $values): array
		{
			return $this->fallback->convertToRowData($this->removePrefix($table), $values);
		}


		public function convertFromRowData(string $table, array $data): array
		{
			return $this->fallback->convertFromRowData($this->removePrefix($table), $data);
		}


		protected function removePrefix(string $table): string
		{
			if ($this->prefix !== '' && strncmp($this->prefix, $table, $this->prefixLength) === 0) {
				return substr($table, $this->prefixLength);
			}

			return $table;
		}
	}
