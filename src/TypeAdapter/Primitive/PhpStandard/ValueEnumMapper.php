<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\PhpStandard;

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
	 * @template TEnumValue
	 * @template TEnum of ValueEnum<TEnumValue>
	 */
	#[MapTo(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(ValueEnum::class))]
	public function to(ValueEnum $value): string|int
	{
		return $value->value();
	}

	/**
	 * @template TEnumValue
	 * @template TEnum of ValueEnum<TEnumValue>
	 */
	#[MapFrom(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(ValueEnum::class))]
	public function from(string|int $value, Type $type): ValueEnum
	{
		$enumClass = $type->name;

		try {
			return $enumClass::fromValue($value);
		} catch (ValueNotFoundException) {
			throw new UnexpectedEnumValueException($value, $enumClass::values());
		}
	}
}
