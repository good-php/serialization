<?php

namespace GoodPhp\Serialization\TypeAdapter\Exception;

use Exception;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\PropertyMappingException;
use RuntimeException;
use Throwable;

class CollectionItemMappingException extends RuntimeException
{
	public function __construct(
		public readonly string|int $key,
		Throwable $previous
	) {
		parent::__construct("Could not map item at key '{$key}': {$previous->getMessage()}", 0, $previous);
	}

	public static function rethrow(int|string $key, callable $callback): mixed
	{
		try {
			return $callback();
		} catch (PropertyMappingException $e) {
			throw new self($key . '.' . $e->path, $e->getPrevious());
		} catch (CollectionItemMappingException $e) {
			throw new self($key . '.' . $e->key, $e->getPrevious());
		} catch (Exception $e) {
			throw new self($key, $e);
		}
	}
}
