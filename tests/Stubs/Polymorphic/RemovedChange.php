<?php

namespace Tests\Stubs\Polymorphic;

class RemovedChange implements Change
{
	public function __construct(
		public string $field,
	) {}
}
