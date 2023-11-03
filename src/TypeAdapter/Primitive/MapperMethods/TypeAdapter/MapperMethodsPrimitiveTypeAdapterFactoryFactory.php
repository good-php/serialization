<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter;

use GoodPhp\Reflection\Reflection\ClassReflection;
use GoodPhp\Reflection\Reflection\MethodReflection;
use GoodPhp\Reflection\Reflector;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod\MapperMethodFactory;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use Webmozart\Assert\Assert;

final class MapperMethodsPrimitiveTypeAdapterFactoryFactory
{
	public function __construct(
		private readonly Reflector $reflector,
		private readonly MapperMethodFactory $mapperMethodFactory
	) {}

	public function create(object $adapter): MapperMethodsPrimitiveTypeAdapterFactory
	{
		$reflection = $this->reflector->forType($adapter::class);

		// I don't think it's possible to NOT get a ClassReflection in this case, so a nicer message is useless.
		Assert::isInstanceOf($reflection, ClassReflection::class);

		return new MapperMethodsPrimitiveTypeAdapterFactory(
			$reflection->methods()
				->filter(fn (MethodReflection $method) => $method->attributes()->has(MapTo::class))
				->map(fn (MethodReflection $method) => $this->mapperMethodFactory->createTo(
					$adapter,
					$method,
					/* @phpstan-ignore-next-line argument.type */
					$method->attributes()->sole(MapTo::class),
				)),
			$reflection->methods()
				->filter(fn (MethodReflection $method) => $method->attributes()->has(MapFrom::class))
				->map(fn (MethodReflection $method) => $this->mapperMethodFactory->createFrom(
					$adapter,
					$method,
					/* @phpstan-ignore-next-line argument.type */
					$method->attributes()->sole(MapFrom::class)
				)),
		);
	}
}
