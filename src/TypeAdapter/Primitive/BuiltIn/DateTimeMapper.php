<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn;

use Carbon\Carbon;
use DateTimeInterface;
use GoodPhp\Reflection\Reflection\Attributes\Attributes;
use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance\BaseTypeAcceptedByAcceptanceStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;

final class DateTimeMapper
{
	#[MapTo(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(DateTimeInterface::class))]
	public function to(DateTimeInterface $value, Attributes $attributes): string
	{
		$value = Carbon::createFromInterface($value);

		$dateAttribute = $attributes->sole(Date::class);

		return $dateAttribute ?
			$value->format($dateAttribute->format) :
			Carbon::instance($value)->toISOString();
	}

	#[MapFrom(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(DateTimeInterface::class))]
	public function from(string $value, Type $type): DateTimeInterface
	{
		$dateClass = $type->name;

		return new $dateClass($value);
	}
}
