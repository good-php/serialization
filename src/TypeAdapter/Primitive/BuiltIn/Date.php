<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\BuiltIn;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Date
{
	public function __construct(
		public readonly string $format,
	) {}
}
