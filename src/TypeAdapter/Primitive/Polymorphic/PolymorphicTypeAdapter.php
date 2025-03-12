<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\Polymorphic;

use GoodPhp\Reflection\Type\PrimitiveType;
use GoodPhp\Reflection\Type\Special\MixedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedPolymorphicTypeException;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedTypeException;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use Illuminate\Support\Arr;
use Webmozart\Assert\Assert;

/**
 * @template T of mixed
 *
 * @implements PrimitiveTypeAdapter<T>
 */
class PolymorphicTypeAdapter implements PrimitiveTypeAdapter
{
	/**
	 * @param callable(mixed): string          $typeNameFromValue   Return the serialized type name value for this object
	 * @param array<string, Type|class-string> $typeNameRealTypeMap Map of serialized type name to real reflection types
	 */
	public function __construct(
		private readonly Serializer $serializer,
		private readonly string $serializedTypeNameField,
		private readonly mixed $typeNameFromValue,
		private readonly array $typeNameRealTypeMap,
	) {}

	/**
	 * @return array<string, mixed>
	 */
	public function serialize(mixed $value): array
	{
		// Returns the serialized type name value for this object - e.g. the "polymorphic name"
		$typeName = ($this->typeNameFromValue)($value);

		$serialized = $this->adapterFromTypeName($typeName)->serialize($value);

		Assert::isMap($serialized, 'Polymorphic type must be serialized as an associative array.');

		return [
			...$serialized,
			$this->serializedTypeNameField => $typeName,
		];
	}

	public function deserialize(mixed $value): mixed
	{
		if (!is_array($value) || ($value !== [] && !Arr::isAssoc($value))) {
			throw new UnexpectedTypeException($value, PrimitiveType::array(MixedType::get(), PrimitiveType::string()));
		}

		$typeName = Arr::pull($value, $this->serializedTypeNameField);

		if (!is_string($typeName)) {
			throw new UnexpectedTypeException($typeName, PrimitiveType::string());
		}

		return $this->adapterFromTypeName($typeName)->deserialize($value);
	}

	/**
	 * @return PrimitiveTypeAdapter<mixed>
	 */
	private function adapterFromTypeName(string $typeName): PrimitiveTypeAdapter
	{
		$type = $this->typeNameRealTypeMap[$typeName] ?? throw new UnexpectedPolymorphicTypeException($this->serializedTypeNameField, $typeName, array_keys($this->typeNameRealTypeMap));

		return $this->serializer->adapter(PrimitiveTypeAdapter::class, $type);
	}
}
