<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn\Nullable;

use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;

/**
 * @template T Type being serialized
 *
 * @implements PrimitiveTypeAdapter<T|null>
 */
class NullableTypeAdapter implements PrimitiveTypeAdapter
{
	/**
	 * @param PrimitiveTypeAdapter<T> $delegate
	 */
	public function __construct(
		private readonly PrimitiveTypeAdapter $delegate,
	) {}

	public function serialize(mixed $value): mixed
	{
		if ($value === null) {
			return null;
		}

		return $this->delegate->serialize($value);
	}

	public function deserialize(mixed $value): mixed
	{
		if ($value === null) {
			return null;
		}

		return $this->delegate->deserialize($value);
	}
}
