<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn\Nullable;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Special\NullableType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

/**
 * @implements TypeAdapterFactory<NullableTypeAdapter<mixed>>
 */
class NullableTypeAdapterFactory implements TypeAdapterFactory
{
	/**
	 * @return NullableTypeAdapter<mixed>|null
	 */
	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?NullableTypeAdapter
	{
		if ($typeAdapterType !== PrimitiveTypeAdapter::class || !$type instanceof NullableType) {
			return null;
		}

		return new NullableTypeAdapter(
			$serializer->adapter($typeAdapterType, $type->innerType, $attributes),
		);
	}
}
