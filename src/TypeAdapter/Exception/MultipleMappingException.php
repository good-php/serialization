<?php

namespace GoodPhp\Serialization\TypeAdapter\Exception;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use RuntimeException;

class MultipleMappingException extends RuntimeException
{
	public function __construct(
		public readonly array $exceptions,
	) {
		$exceptionsCount = count($this->exceptions);

		parent::__construct(
			Str::of($this->exceptions[0]->getMessage())
				->when($exceptionsCount >= 2, fn (Stringable $str) => $str->append(' (and ' . ($exceptionsCount - 1) . ' more errors).'))
				->toString()
		);
	}

	public static function map(iterable $items, bool $withKeys, callable $callable): array
	{
		$data = [];
		$exceptions = [];

		foreach ($items as $key => $item) {
			try {
				if (!$exceptions) {
					$result = $callable($item, $key);

					if ($withKeys) {
						foreach ($result as $mapKey => $mapValue) {
							$data[$mapKey] = $mapValue;
						}
					} else {
						$data[$key] = $result;
					}
				} else {
					$callable($item, $key);
				}
			} catch (Exception $e) {
				$exceptions[] = $e;
				$data = [];
			}
		}

		if (empty($exceptions)) {
			return $data;
		}

		if (count($exceptions) === 1) {
			throw $exceptions[0];
		}

		throw new self($exceptions);
	}
}
