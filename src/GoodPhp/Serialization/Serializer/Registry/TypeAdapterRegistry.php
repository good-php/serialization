<?php

namespace GoodPhp\Serialization\Serializer\Registry;

use GoodPhp\Reflection\Reflection\Attributes\ArrayAttributes;
use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

interface TypeAdapterRegistry
{
	/**
	 * @template TypeAdapterType
	 *
	 * @param class-string<TypeAdapterType> $typeAdapterType
	 *
	 * @return TypeAdapterType
	 */
	public function forType(string $typeAdapterType, Serializer $serializer, Type $type, Attributes $attributes = new ArrayAttributes(), TypeAdapterFactory $skipPast = null): TypeAdapter;
}
