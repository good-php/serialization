<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods;

use GoodPhp\Reflection\Reflector\Reflection\ClassReflection;
use GoodPhp\Reflection\Reflector\Reflection\MethodReflection;
use GoodPhp\Reflection\Reflector\Reflector;
use Webmozart\Assert\Assert;

final class MapperMethodsPrimitiveTypeAdapterFactoryFactory
{
	public function __construct(
		private readonly Reflector $reflector,
		private readonly MapperMethodFactory $mapperMethodFactory
	) {
	}

	public function create(object $adapter): MapperMethodsPrimitiveTypeAdapterFactory
	{
		$reflection = $this->reflector->forType(get_class($adapter));

		Assert::isInstanceOf($reflection, ClassReflection::class);

		return new MapperMethodsPrimitiveTypeAdapterFactory(
			resolveToMappers: fn (MapperMethodsPrimitiveTypeAdapterFactory $factory) => $reflection->methods()
				->filter(fn (MethodReflection $method)                                  => $method->attributes()->has(MapTo::class))
				->map(function (MethodReflection $method) use ($adapter, $factory) {
					$attribute = $method->attributes()->sole(MapTo::class);

					return $this->mapperMethodFactory->create(
						$method,
						fn (MethodReflection $method) => $method->parameters()[0]->type(),
						$attribute->acceptanceStrategy,
						$adapter,
						$factory,
					);
				}),
			resolveFromMappers: fn (MapperMethodsPrimitiveTypeAdapterFactory $factory) => $reflection->methods()
				->filter(fn (MethodReflection $method)                                    => $method->attributes()->has(MapFrom::class))
				->map(function (MethodReflection $method) use ($adapter, $factory) {
					$attribute = $method->attributes()->sole(MapFrom::class);

					return $this->mapperMethodFactory->create(
						$method,
						fn (MethodReflection $method) => $method->returnType(),
						$attribute->acceptanceStrategy,
						$adapter,
						$factory
					);
				}),
		);
	}
}
