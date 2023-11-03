<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming;

use GoodPhp\Reflection\Reflection\PropertyReflection;
use Illuminate\Support\Str;

enum BuiltInNamingStrategy implements NamingStrategy
{
	case PRESERVING;
	case CAMEL_CASE;
	case SNAKE_CASE;
	case PASCAL_CASE;

	public function translate(PropertyReflection $property): string
	{
		$name = $property->name();

		return match ($this) {
			self::PRESERVING  => $name,
			self::CAMEL_CASE  => Str::camel($name),
			self::SNAKE_CASE  => Str::snake($name),
			self::PASCAL_CASE => Str::studly($name),
		};
	}
}
