<?php

	declare(strict_types=1);

	namespace Inlm\Mappers;

	use LeanMapper\Caller;
	use LeanMapper\IMapper;
	use LeanMapper\Row;


	class RowMapper implements IMapper
	{
		const FROM_DB_VALUE = 0;
		const TO_DB_VALUE = 1;

		/** @var IMapper */
		private $fallback;

		/** @var array<string, array<string, array<callable|NULL>>> [tableName => column => [convertToRow, convertFromRow]] */
		private $mapping = [];

		/** @var array<string, array<string, array<callable|NULL>>> [tableName => rowField => [convertToRow, convertFromRow]] */
		private $multiMapping = [];


		public function __construct(?IMapper $fallback = NULL)
		{
			$this->fallback = $fallback ? $fallback : new \LeanMapper\DefaultMapper;
		}


		/**
		 * @return static
		 */
		public function registerFieldMapping(string $entity, string $field, ?callable $fromDbValue = NULL, ?callable $toDbValue = NULL)
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
		 * @return static
		 */
		public function registerMultiValueMapping(string $entity, string $field, ?callable $fromDbValue = NULL, ?callable $toDbValue = NULL)
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


		public function getPrimaryKey(string $table): string
		{
			return $this->fallback->getPrimaryKey($table);
		}


		public function getTable(string $entityClass): string
		{
			return $this->fallback->getTable($entityClass);
		}


		public function getEntityClass(string $table, ?Row $row = NULL): string
		{
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


		public function getImplicitFilters(string $entityClass, ?Caller $caller = null)
		{
			return $this->fallback->getImplicitFilters($entityClass, $caller);
		}


		public function convertToRowData(string $table, array $values): array
		{
			if (isset($this->mapping[$table]) || isset($this->multiMapping[$table])) {
				foreach ($values as $column => $value) {
					if ($value === NULL) {
						continue;
					}

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

						$values[$rowField] = call_user_func($this->multiMapping[$table][$rowField][self::FROM_DB_VALUE], $origValues, $rowField);
					}
				}

				return $values;
			}

			return $this->fallback->convertToRowData($table, $values);
		}


		public function convertFromRowData(string $table, array $data): array
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

								$res[(string) $column] = $value;
							}

						} else {
							$res[(string) $rowField] = $value;
						}
					}

					$data = $res;
				}

				foreach ($data as $column => $value) {
					if ($value === NULL) {
						continue;
					}

					if (isset($this->mapping[$table][$column][self::TO_DB_VALUE])) {
						$data[$column] = call_user_func($this->mapping[$table][$column][self::TO_DB_VALUE], $value);
					}
				}

				return $data;
			}

			return $this->fallback->convertFromRowData($table, $data);
		}
	}
