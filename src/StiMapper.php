<?php

	declare(strict_types=1);

	namespace Inlm\Mappers;

	use LeanMapper\Caller;
	use LeanMapper\IMapper;
	use LeanMapper\Row;


	class StiMapper implements IMapper
	{
		const STI_TYPE_COLUMN = 'type';

		/** @var IMapper */
		private $fallback;

		/** @var array<string, array<string|int, string>>  [baseEntity => [type => entity]] */
		private $stiTypes;

		/** @var array<string, string>  [baseEntity => column] */
		private $stiTypeColumns;

		/** @var array<string, string>  [entity => baseEntity] */
		private $stiEntities;


		public function __construct(IMapper $fallback = NULL)
		{
			$this->fallback = $fallback ? $fallback : new \LeanMapper\DefaultMapper;
		}


		/**
		 * @param  string|int $type
		 * @return self
		 */
		public function registerStiType(string $baseEntity, $type, string $entity)
		{
			if (isset($this->stiTypes[$baseEntity][$type])) {
				throw new \Inlm\Mappers\DuplicateException("Type '$type' for entity $baseEntity already exists.");
			}

			if (isset($this->stiEntities[$entity])) {
				throw new \Inlm\Mappers\DuplicateException("Entity $entity is already registered.");
			}

			$this->stiTypes[$baseEntity][$type] = $entity;
			$this->stiEntities[$entity] = $baseEntity;
			return $this;
		}


		/**
		 * @return self
		 */
		public function registerTypeField(string $baseEntity, string $typeField)
		{
			$this->stiTypeColumns[$baseEntity] = $this->getColumn($baseEntity, $typeField);
			return $this;
		}


		public function getPrimaryKey(string $table): string
		{
			return $this->fallback->getPrimaryKey($table);
		}


		public function getTable(string $entityClass): string
		{
			if (isset($this->stiEntities[$entityClass])) {
				$entityClass = $this->stiEntities[$entityClass];
			}

			return $this->fallback->getTable($entityClass);
		}


		public function getEntityClass(string $table, Row $row = NULL): string
		{
			$baseEntity = $this->fallback->getEntityClass($table, NULL);

			if (isset($this->stiTypes[$baseEntity])) {
				if ($row === NULL) {
					return $baseEntity;
				}

				$typeColumn = isset($this->stiTypeColumns[$baseEntity]) ? $this->stiTypeColumns[$baseEntity] : self::STI_TYPE_COLUMN;
				$type = $row->{$typeColumn};

				if (isset($this->stiTypes[$baseEntity][$type])) {
					return $this->stiTypes[$baseEntity][$type];

				} else {
					throw new \Inlm\Mappers\InvalidStateException("Unknow type '$type' for base entity $baseEntity.");
				}
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
			return $this->fallback->getTableByRepositoryClass($repositoryClass);
		}


		public function getImplicitFilters(string $entityClass, Caller $caller = null)
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


		public function applyStiMapping(\LeanMapper\Fluent $fluent, string $entityClass): void
		{
			if (isset($this->stiEntities[$entityClass])) {
				$baseEntity = $this->stiEntities[$entityClass];
				$table = $this->getTable($baseEntity);
				$column = isset($this->stiTypeColumns[$baseEntity]) ? $this->stiTypeColumns[$baseEntity] : self::STI_TYPE_COLUMN;
				$typeValue = NULL;

				foreach ($this->stiTypes[$baseEntity] as $type => $typeEntity) {
					if ($typeEntity === $entityClass) {
						$fluent->where('%n.%n = %s', $table, $column, $type);
						break;
					}
				}
			}
		}
	}
