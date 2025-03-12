<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\Polymorphic;

use InvalidArgumentException;

class ClassPolymorphicTypeAdapterFactoryBuilder
{
	/** @var array<string, class-string> */
	private array $typeNameToClass = [];

	/** @var array<class-string, string> */
	private array $classToTypeName = [];

	/**
	 * @param class-string $parentClassName
	 */
	public function __construct(
		private readonly string $parentClassName,
		private readonly string $serializedTypeNameField,
	) {}

	/**
	 * @param class-string $className
	 */
	public function subClass(string $className, string $typeName): self
	{
		if ($registeredClassName = $this->typeNameToClass[$typeName] ?? null) {
			throw new InvalidArgumentException("Type name '{$typeName}' has already been registered and maps to class '{$registeredClassName}'.");
		}

		if ($registeredType = $this->classToTypeName[$className] ?? null) {
			throw new InvalidArgumentException("Class '{$className}' has already been registered and maps to type '{$registeredType}'.");
		}

		$this->typeNameToClass[$typeName] = $className;
		$this->classToTypeName[$className] = $typeName;

		return $this;
	}

	public function build(): ClassPolymorphicTypeAdapterFactory
	{
		return new ClassPolymorphicTypeAdapterFactory(
			parentClassName: $this->parentClassName,
			serializedTypeNameField: $this->serializedTypeNameField,
			typeNameToClass: $this->typeNameToClass,
			classToTypeName: $this->classToTypeName,
		);
	}
}
