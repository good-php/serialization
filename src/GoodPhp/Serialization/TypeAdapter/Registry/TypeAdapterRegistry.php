<?php

namespace GoodPhp\Serialization\TypeAdapter\Registry;

use GoodPhp\Reflection\Reflector\Reflection\Attributes\Attributes;
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
	public function forType(string $typeAdapterType, Serializer $serializer, Type $type, Attributes $attributes = new Attributes(), TypeAdapterFactory $skipPast = null): TypeAdapter;
}
