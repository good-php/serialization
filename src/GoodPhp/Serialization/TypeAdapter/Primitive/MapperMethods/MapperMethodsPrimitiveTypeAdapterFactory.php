<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods;

use Closure;
use GoodPhp\Reflection\Reflector\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use Illuminate\Support\Collection;

final class MapperMethodsPrimitiveTypeAdapterFactory implements TypeAdapterFactory
{
	/** @var Collection<int, MapperMethod> */
	private readonly Collection $toMappers;

	/** @var Collection<int, MapperMethod> */
	private readonly Collection $fromMappers;

	public function __construct(
		Closure $resolveToMappers,
		Closure $resolveFromMappers,
	) {
		$this->toMappers = $resolveToMappers($this);
		$this->fromMappers = $resolveFromMappers($this);

		assert($this->toMappers || $this->fromMappers);
	}

	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?TypeAdapter
	{
		if ($typeAdapterType !== PrimitiveTypeAdapter::class || !$type instanceof NamedType) {
			return null;
		}

		$toMapper = $this->findMapper(
			$this->toMappers,
			$type,
			$attributes,
			$serializer
		);
		$fromMapper = $this->findMapper(
			$this->fromMappers,
			$type,
			$attributes,
			$serializer
		);

		if (!$toMapper && !$fromMapper) {
			return null;
		}

		$fallbackDelegate = !$toMapper || !$fromMapper ? $serializer->adapter($typeAdapterType, $type, $attributes, $this) : null;

		return new MapperMethodsPrimitiveTypeAdapter(
			toMapper: $toMapper,
			fromMapper: $fromMapper,
			fallbackDelegate: $fallbackDelegate,
			type: $type,
			serializer: $serializer,
		);
	}

	/**
	 * @param Collection<int, MapperMethod> $mappers
	 */
	private function findMapper(Collection $mappers, NamedType $type, Attributes $attributes, Serializer $serializer): ?MapperMethod
	{
		return $mappers
			->filter(
				function (MapperMethod $mapper) use ($serializer, $type) {
					$method = $mapper->typeSubstituter->resolve($type);
					$receivingType = ($mapper->methodValueType)($method);

					return $mapper->acceptanceStrategy->accepts($receivingType, $type, $serializer);
				}
			)
			->first();
	}
}
