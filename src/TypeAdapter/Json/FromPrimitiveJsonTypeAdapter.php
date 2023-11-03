<?php

namespace GoodPhp\Serialization\TypeAdapter\Json;

use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;

/**
 * @template T
 *
 * @implements JsonTypeAdapter<T>
 */
final class FromPrimitiveJsonTypeAdapter implements JsonTypeAdapter
{
	/**
	 * @param PrimitiveTypeAdapter<T> $primitiveDelegate
	 */
	public function __construct(
		private readonly PrimitiveTypeAdapter $primitiveDelegate,
	) {}

	public function serialize(mixed $value): mixed
	{
		return json_encode(
			$this->primitiveDelegate->serialize($value),
			JSON_THROW_ON_ERROR
		);
	}

	public function deserialize(mixed $value): mixed
	{
		return $this->primitiveDelegate->deserialize(
			json_decode($value, true, 512, JSON_THROW_ON_ERROR),
		);
	}
}
