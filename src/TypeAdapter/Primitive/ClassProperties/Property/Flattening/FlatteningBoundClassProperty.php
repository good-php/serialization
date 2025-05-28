<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\Flattening;

use GoodPhp\Reflection\Reflection\Properties\HasProperties;
use GoodPhp\Reflection\Reflection\PropertyReflection;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\BoundClassProperty;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use Webmozart\Assert\Assert;

/**
 * @template-contravariant T of object
 *
 * @implements BoundClassProperty<T>
 */
class FlatteningBoundClassProperty implements BoundClassProperty
{
	/**
	 * @param PropertyReflection<T, HasProperties<T>> $property
	 * @param TypeAdapter<mixed, mixed>               $typeAdapter
	 */
	public function __construct(
		private readonly PropertyReflection $property,
		private readonly TypeAdapter $typeAdapter,
	) {}

	public function serializedName(): ?string
	{
		return null;
	}

	public function serialize(object $object): array
	{
		$value = $this->property->get($object);

		Assert::notNull($value, 'Value for #[Flatten] property cannot be null. This should have been handled by NullableTypeAdapter.');

		$serialized = $this->typeAdapter->serialize($value);

		Assert::isArray($serialized, 'Serialized value for #[Flatten] property must be an array, [' . gettype($serialized) . '] given.');
		Assert::isMap($serialized, 'Serialized value for #[Flatten] property must be an associative array.');

		return $serialized;
	}

	public function deserialize(array $data): array
	{
		// Pass in all the data from the "root" of serialized data,
		// so that the nested flattened object can use any of the fields they wish.
		return [
			$this->property->name() => $this->typeAdapter->deserialize($data),
		];
	}
}
