<?php

	namespace Inlm\Mappers;


	class CamelCaseMapper extends DefaultMapper
	{
		public function getTable($entityClass)
		{
			return lcfirst($this->trimNamespace($entityClass));
		}


		public function getTableByRepositoryClass($repositoryClass)
		{
			$matches = [];

			if (preg_match('#([a-z0-9]+)repository$#i', $repositoryClass, $matches)) {
				return lcfirst($matches[1]);
			}

			throw new InvalidArgumentException('Cannot determine table name.');
		}
	}
