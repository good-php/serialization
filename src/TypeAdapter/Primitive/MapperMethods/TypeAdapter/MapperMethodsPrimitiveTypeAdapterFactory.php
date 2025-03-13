<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\TypeAdapter;

use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapperMethod\MapperMethod;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use GoodPhp\Serialization\TypeAdapter\TypeAdapterFactory;
use Illuminate\Support\Arr;
use Webmozart\Assert\Assert;

/**
 * @implements TypeAdapterFactory<MapperMethodsPrimitiveTypeAdapter<mixed>>
 */
final class MapperMethodsPrimitiveTypeAdapterFactory implements TypeAdapterFactory
{
	public function __construct(
		/** @var list<MapperMethod> */
		private readonly array $toMappers,
		/** @var list<MapperMethod> */
		private readonly array $fromMappers,
	) {
		Assert::true($this->toMappers || $this->fromMappers);
	}

	public function create(string $typeAdapterType, Type $type, Attributes $attributes, Serializer $serializer): ?MapperMethodsPrimitiveTypeAdapter
	{
		if ($typeAdapterType !== PrimitiveTypeAdapter::class || !$type instanceof NamedType) {
			return null;
		}

		$toMapper = $this->findMapper($this->toMappers, $type, $attributes, $serializer);
		$fromMapper = $this->findMapper($this->fromMappers, $type, $attributes, $serializer);

		if (!$toMapper && !$fromMapper) {
			return null;
		}

		$fallbackDelegate = !$toMapper || !$fromMapper ? $serializer->adapter($typeAdapterType, $type, $attributes, $this) : null;

		return new MapperMethodsPrimitiveTypeAdapter(
			toMapper: $toMapper,
			fromMapper: $fromMapper,
			fallbackDelegate: $fallbackDelegate,
			type: $type,
			attributes: $attributes,
			serializer: $serializer,
			skipPast: $this,
		);
	}

	/**
	 * @param list<MapperMethod> $mappers
	 */
	private function findMapper(array $mappers, NamedType $type, Attributes $attributes, Serializer $serializer): ?MapperMethod
	{
		return Arr::first($mappers, fn (MapperMethod $method) => $method->accepts($type, $attributes, $serializer));
	}
}
