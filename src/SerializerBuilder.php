<?php

namespace GoodPhp\Serialization;

use GoodPhp\Reflection\Reflector;
use GoodPhp\Reflection\ReflectorBuilder;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Hydration\ConstructorHydrator;
use GoodPhp\Serialization\Hydration\Hydrator;
use GoodPhp\Serialization\Serializer\Registry\Cache\MemoizingTypeAdapterRegistry;
use GoodPhp\Serialization\Serializer\Registry\Factory\FactoryTypeAdapterRegistryBuilder;
use GoodPhp\Serialization\Serializer\TypeAdapterRegistrySerializer;
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
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\BoundClassPropertyFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\DefaultBoundClassPropertyFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\Illuminate\CollectionMapper;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod\DefaultMapperMethodFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod\MapperMethodFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter\MapperMethodsPrimitiveTypeAdapterFactoryFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\PhpStandard\ValueEnumMapper;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use Webmozart\Assert\Assert;

final class SerializerBuilder
{
	private ?FactoryTypeAdapterRegistryBuilder $typeAdapterRegistryBuilder = null;

	private ?Reflector $reflector = null;

	private ?NamingStrategy $namingStrategy = null;

	private ?Hydrator $hydrator = null;

	private ?BoundClassPropertyFactory $boundClassPropertyFactory = null;

	private ?MapperMethodFactory $mapperMethodFactory = null;

	public function withReflector(Reflector $reflector): self
	{
		Assert::null($this->reflector, 'You must set the reflector before adding any mappers or factories.');

		$that = clone $this;
		$that->reflector = $reflector;

		return $that;
	}

	public function withNamingStrategy(NamingStrategy $namingStrategy): self
	{
		$that = clone $this;
		$that->namingStrategy = $namingStrategy;

		return $that;
	}

	public function withHydrator(Hydrator $hydrator): self
	{
		$that = clone $this;
		$that->hydrator = $hydrator;

		return $that;
	}

	public function withBoundClassPropertyFactory(BoundClassPropertyFactory $boundClassPropertyFactory): self
	{
		$that = clone $this;
		$that->boundClassPropertyFactory = $boundClassPropertyFactory;

		return $that;
	}

	public function withMapperMethodFactory(MapperMethodFactory $mapperMethodFactory): self
	{
		Assert::null($this->mapperMethodFactory, 'You must set the mapper method factory before adding any mappers or factories.');

		$that = clone $this;
		$that->mapperMethodFactory = $mapperMethodFactory;

		return $that;
	}

	public function addFactory(TypeAdapterFactory $factory): self
	{
		$that = clone $this;
		$that->typeAdapterRegistryBuilder = $that->typeAdapterRegistryBuilder()->addFactory($factory);

		return $that;
	}

	public function addMapper(object $adapter): self
	{
		$that = clone $this;
		$that->typeAdapterRegistryBuilder = $that->typeAdapterRegistryBuilder()->addMapper($adapter);

		return $that;
	}

	/**
	 * @param class-string<object> $attribute
	 */
	public function add(string $typeAdapterType, Type $type, string $attribute, TypeAdapter $adapter): self
	{
		$that = clone $this;
		$that->typeAdapterRegistryBuilder = $that->typeAdapterRegistryBuilder()->add($typeAdapterType, $type, $attribute, $adapter);

		return $that;
	}

	public function addFactoryLast(TypeAdapterFactory $factory): self
	{
		$that = clone $this;
		$that->typeAdapterRegistryBuilder = $that->typeAdapterRegistryBuilder()->addFactoryLast($factory);

		return $that;
	}

	public function addMapperLast(object $adapter): self
	{
		$that = clone $this;
		$that->typeAdapterRegistryBuilder = $that->typeAdapterRegistryBuilder()->addMapperLast($adapter);

		return $that;
	}

	/**
	 * @param class-string<object> $attribute
	 */
	public function addLast(string $typeAdapterType, Type $type, string $attribute, TypeAdapter $adapter): self
	{
		$that = clone $this;
		$that->typeAdapterRegistryBuilder = $that->typeAdapterRegistryBuilder()->addLast($typeAdapterType, $type, $attribute, $adapter);

		return $that;
	}

	public function build(): Serializer
	{
		$typeAdapterRegistryBuilder = $this->typeAdapterRegistryBuilder()
			->addFactoryLast(new NullableTypeAdapterFactory())
			->addMapperLast(new ScalarMapper())
			->addMapperLast(new BackedEnumMapper())
			->addMapperLast(new ValueEnumMapper())
			->addMapperLast(new ArrayMapper())
			->addMapperLast(new CollectionMapper())
			->addMapperLast(new DateTimeMapper())
			->addFactoryLast(new ClassPropertiesPrimitiveTypeAdapterFactory(
				new SerializedNameAttributeNamingStrategy($this->namingStrategy ?? BuiltInNamingStrategy::PRESERVING),
				$this->hydrator ?? new ConstructorHydrator(),
				$this->boundClassPropertyFactory ?? new DefaultBoundClassPropertyFactory(),
			))
			->addFactoryLast(new FromPrimitiveJsonTypeAdapterFactory());

		return new TypeAdapterRegistrySerializer(
			new MemoizingTypeAdapterRegistry($typeAdapterRegistryBuilder->build()),
			$this->reflector()
		);
	}

	private function typeAdapterRegistryBuilder(): FactoryTypeAdapterRegistryBuilder
	{
		return $this->typeAdapterRegistryBuilder ??= new FactoryTypeAdapterRegistryBuilder(
			new MapperMethodsPrimitiveTypeAdapterFactoryFactory(
				$this->reflector(),
				$this->mapperMethodFactory ?? new DefaultMapperMethodFactory(),
			),
		);
	}

	private function reflector(): Reflector
	{
		return $this->reflector ??= (new ReflectorBuilder())
			->withFileCache()
			->withMemoryCache()
			->build();
	}
}
