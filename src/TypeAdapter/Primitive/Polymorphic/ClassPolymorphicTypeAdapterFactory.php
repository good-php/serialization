<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\Polymorphic;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use Webmozart\Assert\Assert;

/**
 * @implements TypeAdapterFactory<PolymorphicTypeAdapter<object>>
 */
class ClassPolymorphicTypeAdapterFactory implements TypeAdapterFactory
{
	/**
	 * @param class-string                $parentClassName
	 * @param array<string, class-string> $typeNameToClass
	 * @param array<class-string, string> $classToTypeName
	 */
	public function __construct(
		private readonly string $parentClassName,
		private readonly string $serializedTypeNameField,
		private readonly array $typeNameToClass,
		private readonly array $classToTypeName,
	) {}

	/**
	 * @param class-string $parentClassName
	 */
	public static function for(string $parentClassName, string $serializedTypeNameField = '__typename'): ClassPolymorphicTypeAdapterFactoryBuilder
	{
		return new ClassPolymorphicTypeAdapterFactoryBuilder($parentClassName, $serializedTypeNameField);
	}

	/**
	 * @return PolymorphicTypeAdapter<object>|null
	 */
	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?PolymorphicTypeAdapter
	{
		if (
			$typeAdapterType !== PrimitiveTypeAdapter::class ||
			!$type instanceof NamedType ||
			$type->name !== $this->parentClassName
		) {
			return null;
		}

		/** @var PolymorphicTypeAdapter<object> */
		return new PolymorphicTypeAdapter(
			serializer: $serializer,
			serializedTypeNameField: $this->serializedTypeNameField,
			typeNameFromValue: function (mixed $value) {
				Assert::object($value, 'Serializable polymorphic type must be an object.');

				$className = $value::class;
				$typeName = $this->classToTypeName[$className] ?? null;

				Assert::notNull($typeName, "Serializable polymorphic class '{$className}' was not registered.");

				return $typeName;
			},
			typeNameRealTypeMap: $this->typeNameToClass,
		);
	}
}
