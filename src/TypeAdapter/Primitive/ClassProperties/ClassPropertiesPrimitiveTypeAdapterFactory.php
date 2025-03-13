<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Reflection\ClassReflection;
use GoodPhp\Reflection\Reflection\PropertyReflection;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Hydration\Hydrator;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\NamingStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\BoundClassPropertyFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

/**
 * @implements TypeAdapterFactory<ClassPropertiesPrimitiveTypeAdapter<object>>
 */
final class ClassPropertiesPrimitiveTypeAdapterFactory implements TypeAdapterFactory
{
	public function __construct(
		private readonly NamingStrategy $namingStrategy,
		private readonly Hydrator $hydrator,
		private readonly BoundClassPropertyFactory $boundClassPropertyFactory,
	) {}

	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?ClassPropertiesPrimitiveTypeAdapter
	{
		if ($typeAdapterType !== PrimitiveTypeAdapter::class || !$type instanceof NamedType) {
			return null;
		}

		$reflection = $serializer->reflector()->forNamedType($type);

		if (!$reflection instanceof ClassReflection) {
			return null;
		}

		/** @var class-string<object> $className */
		$className = $reflection->qualifiedName();

		return new ClassPropertiesPrimitiveTypeAdapter(
			$this->hydrator,
			$className,
			array_map(function (PropertyReflection $property) use ($serializer, $typeAdapterType) {
				$serializedName = $this->namingStrategy->translate($property);

				return PropertyMappingException::rethrow(
					$serializedName,
					fn () => $this->boundClassPropertyFactory->create($typeAdapterType, $serializedName, $property, $serializer),
				);
			}, $reflection->properties())
		);
	}
}
