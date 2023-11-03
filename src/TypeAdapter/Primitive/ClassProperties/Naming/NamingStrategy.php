<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming;

use GoodPhp\Reflection\Reflection\PropertyReflection;

interface NamingStrategy
{
	public function translate(PropertyReflection $property): string;
}
