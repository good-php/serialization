<?php

namespace Tests\Stubs;

use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\UseDefaultForUnexpected;

class UseDefaultStub
{
	public function __construct(
		#[UseDefaultForUnexpected]
		public ?BackedEnumStub $null = null,
		#[UseDefaultForUnexpected]
		public BackedEnumStub $enum = BackedEnumStub::ONE,
	) {}
}
