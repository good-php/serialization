<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\PhpStandard;

use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedEnumValueException;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance\BaseTypeAcceptedByAcceptanceStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use TenantCloud\Standard\Enum\ValueEnum;
use TenantCloud\Standard\Enum\ValueNotFoundException;

/**
 * {@see ValueEnum}.
 */
final class ValueEnumMapper
{
	/**
	 * @template TEnumValue of string|int
	 * @template TEnum of ValueEnum<TEnumValue>
	 *
	 * @param TEnum $value
	 *
	 * @return TEnumValue
	 */
	#[MapTo(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(ValueEnum::class))]
	public function to(ValueEnum $value): string|int
	{
		return $value->value();
	}

	/**
	 * @param NamedType $type
	 *
	 * @return ValueEnum<string|int>
	 */
	#[MapFrom(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(ValueEnum::class))]
	public function from(string|int $value, Type $type): ValueEnum
	{
		/** @var class-string<ValueEnum<string|int>> $enumClass */
		$enumClass = $type->name;

		try {
			return $enumClass::fromValue($value);
		} catch (ValueNotFoundException) {
			throw new UnexpectedEnumValueException($value, $enumClass::values());
		}
	}
}
