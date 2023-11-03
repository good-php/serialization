<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties;

use Exception;
use GoodPhp\Serialization\TypeAdapter\Exception\CollectionItemMappingException;
use RuntimeException;
use Throwable;

class PropertyMappingException extends RuntimeException
{
	public function __construct(
		public readonly string $path,
		public readonly Throwable $previous,
	) {
		parent::__construct("Could not map property at path '{$path}': {$previous->getMessage()}", 0, $previous);
	}

	/**
	 * @template TReturn
	 *
	 * @param callable(): TReturn $callback
	 *
	 * @return TReturn
	 */
	public static function rethrow(?string $serializedName, callable $callback): mixed
	{
		try {
			return $callback();
		} catch (PropertyMappingException $e) {
			throw new self($serializedName !== null ? $serializedName . '.' . $e->path : $e->path, $e->previous);
		} catch (CollectionItemMappingException $e) {
			throw new self($serializedName !== null ? $serializedName . '.' . $e->key : (string) $e->key, $e->previous);
		} catch (Exception $e) {
			throw $serializedName !== null ? new self($serializedName, $e) : $e;
		}
	}
}
