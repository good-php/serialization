<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn;

use BackedEnum;
use GoodPhp\Reflection\Type\NamedType;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\TypeAdapter\Exception\UnexpectedEnumValueException;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance\BaseTypeAcceptedByAcceptanceStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;

final class BackedEnumMapper
{
	#[MapTo(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(BackedEnum::class))]
	public function to(BackedEnum $value): string|int
	{
		return $value->value;
	}

	/**
	 * @param NamedType $type
	 */
	#[MapFrom(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(BackedEnum::class))]
	public function from(string|int $value, Type $type): BackedEnum
	{
		/** @var class-string<BackedEnum> $enumClass */
		$enumClass = $type->name;
		$enum = $enumClass::tryFrom($value);

		// null is returned when there is no enum case associated with that value.
		if ($enum === null) {
			throw new UnexpectedEnumValueException($value, array_column($enumClass::cases(), 'value'));
		}

		return $enum;
	}
}
