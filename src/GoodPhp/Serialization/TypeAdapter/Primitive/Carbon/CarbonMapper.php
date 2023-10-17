<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\Carbon;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;

class CarbonMapper
{
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function toCarbon(Carbon $value): string
	{
		return $value->toISOString();
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function fromCarbon(string $value): Carbon
	{
		return new Carbon($value);
	}

	#[MapTo(PrimitiveTypeAdapter::class)]
	public function toCarbonImmutable(CarbonImmutable $value): string
	{
		return $value->toISOString();
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function fromCarbonImmutable(string $value): CarbonImmutable
	{
		return new CarbonImmutable($value);
	}
}
