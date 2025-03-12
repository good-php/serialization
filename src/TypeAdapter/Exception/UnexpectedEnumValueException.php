<?php

namespace GoodPhp\Serialization\TypeAdapter\Exception;

use RuntimeException;
use Throwable;

class UnexpectedEnumValueException extends RuntimeException implements UnexpectedValueException
{
	/**
	 * @param list<int|string> $expectedValues
	 */
	public function __construct(
		public readonly string|int $value,
		public readonly array $expectedValues,
		Throwable $previous = null
	) {
		parent::__construct(
			'Expected one of [' .
				implode(', ', $this->expectedValues) .
				"], but got '{$this->value}'",
			0,
			$previous
		);
	}
}
