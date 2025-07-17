<?php

namespace GoodPhp\Serialization\TypeAdapter\Exception;

use GoodPhp\Reflection\Type\Type;
use RuntimeException;
use Throwable;

class UnexpectedTypeException extends RuntimeException
{
	public function __construct(
		public readonly mixed $value,
		public readonly Type $expectedType,
		?Throwable $previous = null
	) {
		parent::__construct(
			"Expected value of type '{$expectedType}', but got '" .
				($value && is_object($value) ? $value::class : gettype($value)) .
				"'",
			0,
			$previous
		);
	}
}
