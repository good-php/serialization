<?php

namespace GoodPhp\Serialization\TypeAdapter\Json;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

/**
 * Serializes to JSON by first serializing to primitives and then just encoding with json_encode().
 *
 * @implements TypeAdapterFactory<JsonTypeAdapter<mixed>>
 */
final class FromPrimitiveJsonTypeAdapterFactory implements TypeAdapterFactory
{
	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?JsonTypeAdapter
	{
		if ($typeAdapterType !== JsonTypeAdapter::class) {
			return null;
		}

		return new FromPrimitiveJsonTypeAdapter(
			$serializer->adapter(PrimitiveTypeAdapter::class, $type, $attributes),
		);
	}
}
