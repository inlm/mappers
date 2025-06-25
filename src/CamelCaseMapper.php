<?php

	declare(strict_types=1);

	namespace Inlm\Mappers;


	class CamelCaseMapper extends DefaultMapper
	{
		public function getTable(string $entityClass): string
		{
			return lcfirst(\LeanMapper\Helpers::trimNamespace($entityClass));
		}


		public function getTableByRepositoryClass(string $repositoryClass): string
		{
			$matches = [];

			if (preg_match('#([a-z0-9]+)repository$#i', $repositoryClass, $matches)) {
				return lcfirst($matches[1]);
			}

			throw new InvalidArgumentException('Cannot determine table name.');
		}
	}
