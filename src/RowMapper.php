<?php

	namespace Inlm\Mappers;

	use LeanMapper\Caller;
	use LeanMapper\IRowMapper;
	use LeanMapper\Row;


	class RowMapper implements IRowMapper
	{
		const FROM_DB_VALUE = 0;
		const TO_DB_VALUE = 1;

		/** @var IRowMapper */
		private $fallback;

		/** @var array [tableName => column => [convertToRow, convertFromRow]] */
		private $mapping = [];


		public function __construct(IRowMapper $fallback = NULL)
		{
			$this->fallback = $fallback ? $fallback : new \LeanMapper\DefaultMapper;
		}


		/**
		 * @param  string
		 * @param  string
		 * @return static
		 */
		public function registerFieldMapping($entity, $field, callable $fromDbValue = NULL, callable $toDbValue = NULL)
		{
			if ($fromDbValue === NULL && $toDbValue === NULL) {
				throw new InvalidArgumentException("Missing convertors for $entity::\$$field, both are NULL.");
			}

			$table = $this->getTable($entity);
			$column = $this->getColumn($entity, $field);

			if (isset($this->mapping[$table][$column])) {
				throw new \Inlm\Mappers\DuplicateException("Convertor for table '$table' and column '$column' (field $entity::\$$field) already exists.");
			}

			$this->mapping[$table][$column] = [
				self::FROM_DB_VALUE => $fromDbValue,
				self::TO_DB_VALUE => $toDbValue
			];
			return $this;
		}


		public function getPrimaryKey($table)
		{
			return $this->fallback->getPrimaryKey($table);
		}


		public function getTable($entityClass)
		{
			return $this->fallback->getTable($entityClass);
		}


		public function getEntityClass($table, Row $row = NULL)
		{
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
			if (isset($this->mapping[$table])) {
				$res = [];

				foreach ($values as $column => $value) {
					if (isset($this->mapping[$table][$column][self::FROM_DB_VALUE])) {
						$res[$column] = call_user_func($this->mapping[$table][$column][self::FROM_DB_VALUE], $value);

					} else {
						$res[$column] = $value;
					}
				}

				return $res;
			}

			return $this->fallback->convertToRowData($table, $values);
		}


		public function convertFromRowData($table, array $data)
		{
			if (isset($this->mapping[$table])) {
				$res = [];

				foreach ($data as $column => $value) {
					if (isset($this->mapping[$table][$column][self::TO_DB_VALUE])) {
						$res[$column] = call_user_func($this->mapping[$table][$column][self::TO_DB_VALUE], $value);

					} else {
						$res[$column] = $value;
					}
				}

				return $res;
			}

			return $this->fallback->convertFromRowData($table, $data);
		}
	}
