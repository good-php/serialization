<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn\Nullable;

use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;

class NullableTypeAdapter implements PrimitiveTypeAdapter
{
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
