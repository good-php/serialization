<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn\Nullable;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Special\NullableType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

class NullableTypeAdapterFactory implements TypeAdapterFactory
{
	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?TypeAdapter
	{
		if ($typeAdapterType !== PrimitiveTypeAdapter::class || !$type instanceof NullableType) {
			return null;
		}

		return new NullableTypeAdapter(
			$serializer->adapter($typeAdapterType, $type->innerType, $attributes),
		);
	}
}
