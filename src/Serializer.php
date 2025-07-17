<?php

namespace GoodPhp\Serialization;

use GoodPhp\Reflection\Reflection\Attributes\ArrayAttributes;
use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Reflector;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

interface Serializer
{
	public function reflector(): Reflector;

	/**
	 * Get an adapter for specified type.
	 *
	 * It'd be nice to have generic $type here, but it's not possible.
	 *
	 * @template A of TypeAdapter<mixed, mixed>
	 *
	 * @param class-string<A>                                    $typeAdapterType
	 * @param Type|class-string                                  $type
	 * @param TypeAdapterFactory<TypeAdapter<mixed, mixed>>|null $skipPast
	 *
	 * @return A
	 */
	public function adapter(string $typeAdapterType, Type|string $type, Attributes $attributes = new ArrayAttributes(), ?TypeAdapterFactory $skipPast = null): TypeAdapter;
}
