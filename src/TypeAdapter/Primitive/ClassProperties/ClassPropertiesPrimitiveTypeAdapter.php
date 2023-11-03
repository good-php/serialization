<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties;

use GoodPhp\Reflection\Type\PrimitiveType;
use GoodPhp\Reflection\Type\Special\MixedType;
use GoodPhp\Serialization\Hydration\Hydrator;
use GoodPhp\Serialization\TypeAdapter\Exception\MultipleMappingException;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedTypeException;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\BoundClassProperty;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @template T of object
 *
 * @implements PrimitiveTypeAdapter<T>
 */
final class ClassPropertiesPrimitiveTypeAdapter implements PrimitiveTypeAdapter
{
	/**
	 * @param class-string<T>                        $className
	 * @param Collection<int, BoundClassProperty<T>> $properties
	 */
	public function __construct(
		private readonly Hydrator $hydrator,
		private readonly string $className,
		private readonly Collection $properties,
	) {}

	public function serialize(mixed $value): mixed
	{
		return MultipleMappingException::map(
			$this->properties,
			true,
			fn (BoundClassProperty $property) => PropertyMappingException::rethrow(
				$property->serializedName(),
				fn () => $property->serialize($value)
			)
		);
	}

	public function deserialize(mixed $value): mixed
	{
		if (!is_array($value) || ($value !== [] && !Arr::isAssoc($value))) {
			throw new UnexpectedTypeException($value, PrimitiveType::array(MixedType::get(), PrimitiveType::string()));
		}

		/** @var array<string, mixed> $value */
		$properties = MultipleMappingException::map(
			$this->properties,
			true,
			fn (BoundClassProperty $property) => PropertyMappingException::rethrow(
				$property->serializedName(),
				fn () => $property->deserialize($value)
			)
		);

		return $this->hydrator->hydrate($this->className, $properties);
	}
}
