<?php

namespace Tests\Stubs;

use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\BuiltInNamingStrategy;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\SerializedName;

#[SerializedName(BuiltInNamingStrategy::PASCAL_CASE)]
class NestedStub
{
	public function __construct(
		public string $field = 'something',
	) {}
}
