<?php

namespace GoodPhp\Serialization;

use GoodPhp\Reflection\Reflector\Reflector;
use GoodPhp\Reflection\ReflectorBuilder;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Hydration\ConstructorHydrator;
use GoodPhp\Serialization\TypeAdapter\Json\FromPrimitiveJsonTypeAdapterFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn\ArrayMapper;
use GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn\BackedEnumMapper;
use GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn\DateTimeMapper;
use GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn\Nullable\NullableTypeAdapterFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn\ScalarMapper;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\ClassPropertiesPrimitiveTypeAdapterFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\BuiltInNamingStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\NamingStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\SerializedNameAttributeNamingStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\DefaultBoundClassPropertyFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\Illuminate\CollectionMapper;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethodFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethodsPrimitiveTypeAdapterFactoryFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethodTypeSubstiter\MapperMethodTypeSubstituterFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\PhpStandard\OptionalMapper;
use GoodPhp\Serialization\TypeAdapter\Primitive\PhpStandard\ValueEnumMapper;
use GoodPhp\Serialization\TypeAdapter\Registry\Factory\FactoryTypeAdapterRegistryBuilder;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;

final class SerializerBuilder
{
	private FactoryTypeAdapterRegistryBuilder $typeAdapterRegistryBuilder;

	private Reflector $reflector;

	private ?NamingStrategy $namingStrategy;

	public function __construct(Reflector $reflector = null)
	{
		$this->reflector = $reflector ?? (new ReflectorBuilder())->build();

		$this->typeAdapterRegistryBuilder = new FactoryTypeAdapterRegistryBuilder(
			new MapperMethodsPrimitiveTypeAdapterFactoryFactory(
				$this->reflector,
				new MapperMethodFactory(
					new MapperMethodTypeSubstituterFactory(),
				),
			),
		);
	}

	public function namingStrategy(NamingStrategy $namingStrategy): self
	{
		$this->namingStrategy = $namingStrategy;

		return $this;
	}

	public function addFactory(TypeAdapterFactory $factory): self
	{
		$this->typeAdapterRegistryBuilder->addFactory($factory);

		return $this;
	}

	public function addMapper(object $adapter): self
	{
		$this->typeAdapterRegistryBuilder->addMapper($adapter);

		return $this;
	}

	/**
	 * @param class-string<object> $attribute
	 */
	public function add(string $typeAdapterType, Type $type, string $attribute, TypeAdapter $adapter): self
	{
		$this->typeAdapterRegistryBuilder->add($typeAdapterType, $type, $attribute, $adapter);

		return $this;
	}

	public function addFactoryLast(TypeAdapterFactory $factory): self
	{
		$this->typeAdapterRegistryBuilder->addFactoryLast($factory);

		return $this;
	}

	public function addMapperLast(object $adapter): self
	{
		$this->typeAdapterRegistryBuilder->addMapperLast($adapter);

		return $this;
	}

	/**
	 * @param class-string<object> $attribute
	 */
	public function addLast(string $typeAdapterType, Type $type, string $attribute, TypeAdapter $adapter): self
	{
		$this->typeAdapterRegistryBuilder->addLast($typeAdapterType, $type, $attribute, $adapter);

		return $this;
	}

	public function build(): Serializer
	{
		$this->typeAdapterRegistryBuilder
			->addFactoryLast(new NullableTypeAdapterFactory())
			->addMapperLast(new ScalarMapper())
			->addMapperLast(new BackedEnumMapper())
			->addMapperLast(new ValueEnumMapper())
			->addMapperLast(new ArrayMapper())
			->addMapperLast(new CollectionMapper())
			->addMapperLast(new DateTimeMapper())
			->addFactoryLast(new ClassPropertiesPrimitiveTypeAdapterFactory(
				new SerializedNameAttributeNamingStrategy($this->namingStrategy ?? BuiltInNamingStrategy::PRESERVING),
				new ConstructorHydrator(),
				new DefaultBoundClassPropertyFactory(),
			))
			->addFactoryLast(new FromPrimitiveJsonTypeAdapterFactory());

		return new Serializer(
			$this->typeAdapterRegistryBuilder->build(),
			$this->reflector
		);
	}
}
