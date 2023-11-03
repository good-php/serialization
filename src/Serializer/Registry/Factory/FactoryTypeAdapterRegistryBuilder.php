<?php

namespace GoodPhp\Serialization\Serializer\Registry\Factory;

use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter\MapperMethodsPrimitiveTypeAdapterFactoryFactory;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

final class FactoryTypeAdapterRegistryBuilder
{
	/** @var array<int, TypeAdapterFactory<TypeAdapter<mixed, mixed>>> */
	private array $factories = [];

	public function __construct(
		private readonly MapperMethodsPrimitiveTypeAdapterFactoryFactory $mapperMethodsTypeAdapterFactoryFactory,
	) {}

	/**
	 * @param TypeAdapterFactory<TypeAdapter<mixed, mixed>> $factory
	 */
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
	 * @param TypeAdapterFactory<TypeAdapter<mixed, mixed>> $factory
	 */
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

	public function build(): FactoryTypeAdapterRegistry
	{
		return new FactoryTypeAdapterRegistry($this->factories);
	}
}
