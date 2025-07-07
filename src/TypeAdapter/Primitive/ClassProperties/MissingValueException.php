<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties;

use RuntimeException;
use Throwable;

class MissingValueException extends RuntimeException
{
	public function __construct(?Throwable $previous = null)
	{
		parent::__construct('Missing value.', 0, $previous);
	}
}
