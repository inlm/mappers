<?php

	namespace Inlm\Mappers;

	use LeanMapper;


	class DefaultMapper extends LeanMapper\DefaultMapper
	{
		/**
		 * @param  string|NULL
		 */
		public function __construct($defaultEntityNamespace = NULL)
		{
			if (is_string($defaultEntityNamespace)) {
				$this->defaultEntityNamespace = $defaultEntityNamespace;
			}
		}
	}
