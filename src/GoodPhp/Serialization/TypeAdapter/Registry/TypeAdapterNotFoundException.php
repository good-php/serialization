<?php

namespace GoodPhp\Serialization\TypeAdapter\Registry;

use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use RuntimeException;
use Throwable;

final class TypeAdapterNotFoundException extends RuntimeException
{
	public function __construct(string $typeAdapterType, Type $type, array $attributes, ?TypeAdapterFactory $skipPast, string|int $code = 0, Throwable $previous = null)
	{
		$message = "A matching type adapter of type '{$typeAdapterType}' for type '{$type}' " .
			($attributes ? 'with attributes #[' . implode(', ', array_map(fn (object $attribute) => get_class($attribute), $attributes)) . '] ' : '') .
			($skipPast ? 'skipping past ' . get_class($skipPast) . ' ' : '') .
			'was not found.';

		parent::__construct($message, $code, $previous);
	}
}
