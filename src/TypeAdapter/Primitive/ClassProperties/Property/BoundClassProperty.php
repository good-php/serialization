<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property;

/**
 * @template-contravariant T of object
 */
interface BoundClassProperty
{
	/**
	 * Serialize object property into a serialized array.
	 * It may be empty if property needs to be excluded from the serialized form.
	 *
	 * @param T $object
	 *
	 * @return array<string, mixed> Resulting serialized data to be merged
	 */
	public function serialize(object $object): array;

	/**
	 * Deserialize object property from arrayed data into an array of object properties.
	 * It may be empty if property needs to be excluded from being set on hydration.
	 *
	 * @param array<string, mixed> $data Serialized data
	 *
	 * @return array<string, mixed> Resulting deserialized data with keys being property names
	 */
	public function deserialize(array $data): array;

	public function serializedName(): ?string;
}
