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

		/** @var array [tableName => rowField => [convertToRow, convertFromRow]] */
		private $multiMapping = [];


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


		/**
		 * @param  string
		 * @param  string
		 * @return static
		 */
		public function registerMultiValueMapping($entity, $field, callable $fromDbValue = NULL, callable $toDbValue = NULL)
		{
			if ($fromDbValue === NULL && $toDbValue === NULL) {
				throw new InvalidArgumentException("Missing convertors for $entity::\$$field, both are NULL.");
			}

			$table = $this->getTable($entity);
			$rowField = $this->getColumn($entity, $field);

			if (isset($this->multiMapping[$table][$rowField])) {
				throw new \Inlm\Mappers\DuplicateException("Multi convertor for table '$table' and row field '$rowField' ($entity::\$$field) already exists.");
			}

			$this->multiMapping[$table][$rowField] = [
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
			if (isset($this->mapping[$table]) || isset($this->multiMapping[$table])) {
				foreach ($values as $column => $value) {
					if (isset($this->mapping[$table][$column][self::FROM_DB_VALUE])) {
						$values[$column] = call_user_func($this->mapping[$table][$column][self::FROM_DB_VALUE], $value);
					}
				}

				if (isset($this->multiMapping[$table])) {
					$origValues = $values;

					foreach ($this->multiMapping[$table] as $rowField => $convertors) {
						if (!isset($this->multiMapping[$table][$rowField][self::FROM_DB_VALUE])) {
							continue;
						}

						if (array_key_exists($rowField, $values)) {
							throw new DuplicateException("Row field '$rowField' already exists.");
						}

						$values[$rowField] = call_user_func($this->multiMapping[$table][$rowField][self::FROM_DB_VALUE], $origValues, $rowField);
					}
				}

				return $values;
			}

			return $this->fallback->convertToRowData($table, $values);
		}


		public function convertFromRowData($table, array $data)
		{
			if (isset($this->mapping[$table]) || isset($this->multiMapping[$table])) {
				if (isset($this->multiMapping[$table])) {
					$res = [];

					foreach ($data as $rowField => $value) {
						if (isset($this->multiMapping[$table][$rowField][self::TO_DB_VALUE])) {
							$fieldValues = call_user_func($this->multiMapping[$table][$rowField][self::TO_DB_VALUE], $value, $rowField);

							if (!is_array($fieldValues)) {
								throw new InvalidStateException('Return value from multi convertor must be array.');
							}

							foreach ($fieldValues as $column => $value) {
								// if (array_key_exists($column, $data)) {
									// throw new InvalidStateException("Column '$column' already exists in result.");
								// }

								$res[$column] = $value;
							}

						} else {
							$res[$rowField] = $value;
						}
					}

					$data = $res;
				}

				foreach ($data as $column => $value) {
					if (isset($this->mapping[$table][$column][self::TO_DB_VALUE])) {
						$data[$column] = call_user_func($this->mapping[$table][$column][self::TO_DB_VALUE], $value);
					}
				}

				return $data;
			}

			return $this->fallback->convertFromRowData($table, $data);
		}
	}
