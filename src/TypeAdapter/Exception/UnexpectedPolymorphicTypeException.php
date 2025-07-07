<?php

namespace GoodPhp\Serialization\TypeAdapter\Exception;

use RuntimeException;
use Throwable;

class UnexpectedPolymorphicTypeException extends RuntimeException implements UnexpectedValueException
{
	/**
	 * @param list<string> $expectedTypeNames
	 */
	public function __construct(
		public readonly string $typeNameField,
		public readonly string $value,
		public readonly array $expectedTypeNames,
		?Throwable $previous = null
	) {
		parent::__construct(
			"Only the following polymorphic types for field '{$this->typeNameField}' are allowed: [" .
				implode(', ', $this->expectedTypeNames) .
				"], but got '{$this->value}'",
			0,
			$previous
		);
	}
}
