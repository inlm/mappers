<?php

	namespace Inlm\Mappers;


	class CamelCaseMapper extends DefaultMapper
	{
		/**
		 * @inheritdoc
		 */
		public function getTable($entityClass)
		{
			return lcfirst($this->trimNamespace($entityClass));
		}



		/**
		 * @inheritdoc
		 */
		public function getTableByRepositoryClass($repositoryClass)
		{
			$matches = array();

			if (preg_match('#([a-z0-9]+)repository$#i', $repositoryClass, $matches)) {
				return lcfirst($matches[1]);
			}

			throw new InvalidArgumentException('Cannot determine table name.');
		}
	}
