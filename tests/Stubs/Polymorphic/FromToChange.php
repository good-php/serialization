<?php

namespace Tests\Stubs\Polymorphic;

class FromToChange implements Change
{
	public function __construct(
		public string $from,
		public string $to,
	) {}
}
