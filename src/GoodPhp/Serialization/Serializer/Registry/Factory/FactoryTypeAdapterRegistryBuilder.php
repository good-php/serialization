<?php

namespace GoodPhp\Serialization\Serializer\Registry\Factory;

use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\TypeAdapter\MatchingDelegate\MatchingDelegateTypeAdapterFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter\MapperMethodsPrimitiveTypeAdapterFactoryFactory;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

final class FactoryTypeAdapterRegistryBuilder
{
	/** @var TypeAdapterFactory[] */
	private array $factories = [];

	public function __construct(
		private readonly MapperMethodsPrimitiveTypeAdapterFactoryFactory $mapperMethodsTypeAdapterFactoryFactory,
	) {
	}

	public function addFactory(TypeAdapterFactory $factory): self
	{
		$that = clone $this;
		array_unshift($that->factories, $factory);

		return $that;
	}

	public function addMapper(object $adapter): self
	{
		return $this->addFactory($this->mapperMethodsTypeAdapterFactoryFactory->create($adapter));
	}

	/**
	 * @param class-string<object> $attribute
	 */
	public function add(string $typeAdapterType, Type $type, string $attribute, TypeAdapter $adapter): self
	{
		return $this->addFactory(new MatchingDelegateTypeAdapterFactory($typeAdapterType, $type, $attribute, $adapter));
	}

	public function addFactoryLast(TypeAdapterFactory $factory): self
	{
		$that = clone $this;
		$that->factories[] = $factory;

		return $that;
	}

	public function addMapperLast(object $adapter): self
	{
		return $this->addFactoryLast($this->mapperMethodsTypeAdapterFactoryFactory->create($adapter));
	}

	/**
	 * @param class-string<object> $attribute
	 */
	public function addLast(string $typeAdapterType, Type $type, string $attribute, TypeAdapter $adapter): self
	{
		return $this->addFactoryLast(new MatchingDelegateTypeAdapterFactory($typeAdapterType, $type, $attribute, $adapter));
	}

	public function build(): FactoryTypeAdapterRegistry
	{
		return new FactoryTypeAdapterRegistry($this->factories);
	}
}
