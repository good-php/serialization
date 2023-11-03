<?php

namespace GoodPhp\Serialization\TypeAdapter\Exception;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use RuntimeException;
use Webmozart\Assert\Assert;

class MultipleMappingException extends RuntimeException
{
	/**
	 * @param array<int, Exception> $exceptions
	 */
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

	/**
	 * @template TKey
	 * @template TValue
	 * @template TReturnKey
	 * @template TReturnValue
	 *
	 * @param iterable<TKey, TValue>                                                    $items
	 * @param callable(TValue, TKey): (iterable<TReturnKey, TReturnValue>|TReturnValue) $callable
	 *
	 * @return ($withKeys is true ? array<TReturnKey, TReturnValue> : array<TKey, TReturnValue>)
	 */
	public static function map(iterable $items, bool $withKeys, callable $callable): array
	{
		$data = [];
		$exceptions = [];

		foreach ($items as $key => $item) {
			try {
				if (!$exceptions) {
					$result = $callable($item, $key);

					if ($withKeys) {
						Assert::isIterable($result);

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
			/* @phpstan-ignore-next-line method.returnType */
			return $data;
		}

		if (count($exceptions) === 1) {
			throw $exceptions[0];
		}

		/* @phpstan-ignore-next-line deadCode.unreachable */
		throw new self($exceptions);
	}
}
