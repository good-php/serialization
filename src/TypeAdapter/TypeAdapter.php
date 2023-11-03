<?php

namespace GoodPhp\Serialization\TypeAdapter;

/**
 * @template DeserializedType Deserialized type
 * @template SerializedType Serialized type
 */
interface TypeAdapter
{
	/**
	 * @param DeserializedType $value
	 *
	 * @return SerializedType
	 */
	public function serialize(mixed $value): mixed;

	/**
	 * @param SerializedType $value
	 *
	 * @return DeserializedType
	 */
	public function deserialize(mixed $value): mixed;
}
