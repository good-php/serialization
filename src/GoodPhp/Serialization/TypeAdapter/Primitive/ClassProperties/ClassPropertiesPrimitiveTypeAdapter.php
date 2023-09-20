<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties;

use GoodPhp\Serialization\Hydration\Hydrator;
use GoodPhp\Serialization\TypeAdapter\Exception\MultipleMappingException;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\DefaultBoundClassProperty;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use Illuminate\Support\Collection;

/**
 * @template T
 */
final class ClassPropertiesPrimitiveTypeAdapter implements PrimitiveTypeAdapter
{
	/**
	 * @param Collection<int, DefaultBoundClassProperty> $properties
	 */
	public function __construct(
		private readonly Hydrator $hydrator,
		private readonly string $className,
		private readonly Collection $properties,
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function serialize(mixed $value): mixed
	{
		return MultipleMappingException::map(
			$this->properties,
			true,
			fn (DefaultBoundClassProperty $property) => PropertyMappingException::rethrow(
				$property,
				fn () => $property->serialize($value)
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function deserialize(mixed $value): mixed
	{
		$properties = MultipleMappingException::map(
			$this->properties,
			true,
			fn (DefaultBoundClassProperty $property) => PropertyMappingException::rethrow(
				$property,
				fn () => $property->deserialize($value)
			)
		);

		return $this->hydrator->hydrate($this->className, $properties);
	}
}
