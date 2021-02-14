<?php

	namespace Inlm\Mappers;

	use LeanMapper\Caller;
	use LeanMapper\IRowMapper;
	use LeanMapper\Row;


	class StiMapper implements IRowMapper
	{
		const STI_TYPE_COLUMN = 'type';

		/** @var IRowMapper */
		private $fallback;

		/** @var array  [baseEntity => [type => entity]] */
		private $stiTypes;

		/** @var array  [baseEntity => column] */
		private $stiTypeColumns;

		/** @var array  [entity => baseEntity] */
		private $stiEntities;


		public function __construct(IRowMapper $fallback = NULL)
		{
			$this->fallback = $fallback ? $fallback : new \LeanMapper\DefaultMapper;
		}


		/**
		 * @param  string
		 * @param  string|int
		 * @param  string
		 * @return static
		 */
		public function registerStiType($baseEntity, $type, $entity)
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
		 * @param  string
		 * @param  string
		 * @return static
		 */
		public function registerTypeField($baseEntity, $typeField)
		{
			$this->stiTypeColumns[$baseEntity] = $this->getColumn($baseEntity, $typeField);
			return $this;
		}


		public function getPrimaryKey($table)
		{
			return $this->fallback->getPrimaryKey($table);
		}


		public function getTable($entityClass)
		{
			if (isset($this->stiEntities[$entityClass])) {
				$entityClass = $this->stiEntities[$entityClass];
			}

			return $this->fallback->getTable($entityClass);
		}


		public function getEntityClass($table, Row $row = NULL)
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


		public function getRelationshipColumn($sourceTable, $targetTable)
		{
			return $this->fallback->getRelationshipColumn($sourceTable, $targetTable);
		}


		public function getTableByRepositoryClass($repositoryClass)
		{
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
			return $this->fallback->convertFromRowData($table, $values);
		}


		public function applyStiMapping(\LeanMapper\Fluent $fluent, $entityClass)
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
