<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\MapperMethods\Acceptance;

use GoodPhp\Reflection\Type\Type;
use GoodPhp\Serialization\Serializer;

interface AcceptanceStrategy
{
	public function accepts(Type $mapperType, Type $type, Serializer $serializer): bool;
}
