<?php

namespace GoodPhp\Serialization\Serializer\Registry;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use RuntimeException;
use Throwable;

final class TypeAdapterNotFoundException extends RuntimeException
{
	/**
	 * @param TypeAdapterFactory<TypeAdapter<mixed, mixed>>|null $skipPast
	 */
	public function __construct(string $typeAdapterType, Type $type, Attributes $attributes, ?TypeAdapterFactory $skipPast, Throwable $previous = null)
	{
		$message = "A matching type adapter of type '{$typeAdapterType}' for type '{$type}' " .
			($attributes->has() ? 'with attributes ' . $attributes : '') .
			($skipPast ? 'skipping past ' . $skipPast::class . ' ' : '') .
			'was not found.';

		parent::__construct($message, 0, $previous);
	}
}
