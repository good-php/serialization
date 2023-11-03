<?php declare(strict_types=1);

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn;

use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapFrom;
use GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\MapTo;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;

class ScalarMapper
{
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function toInt(int $value): int
	{
		return $value;
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function intFrom(int $value): int
	{
		return $value;
	}

	#[MapTo(PrimitiveTypeAdapter::class)]
	public function toFloat(float $value): float
	{
		return $value;
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function floatFrom(float $value): float
	{
		return $value;
	}

	#[MapTo(PrimitiveTypeAdapter::class)]
	public function toBool(bool $value): bool
	{
		return $value;
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function boolFrom(bool $value): bool
	{
		return $value;
	}

	#[MapTo(PrimitiveTypeAdapter::class)]
	public function toString(string $value): string
	{
		return $value;
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function stringFrom(string $value): string
	{
		return $value;
	}
}
