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
	 * @template T
	 * @template A of TypeAdapter<T>
	 *
	 * @param class-string<T>      $typeAdapterType
	 * @param Type|class-string<T> $type
	 *
	 * @return A
	 */
	public function adapter(string $typeAdapterType, Type|string $type, Attributes $attributes = new ArrayAttributes(), TypeAdapterFactory $skipPast = null): TypeAdapter;
}
