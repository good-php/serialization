<?php

namespace GoodPhp\Serialization\TypeAdapter;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter as T;

/**
 * @template-covariant T of TypeAdapter
 */
interface TypeAdapterFactory
{
	/**
	 * @param class-string<TypeAdapter<mixed, mixed>> $typeAdapterType
	 *
	 * @return TypeAdapter<mixed, mixed>|null
	 */
	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?TypeAdapter;
}
