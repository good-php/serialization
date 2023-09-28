<?php

namespace GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties;

use Exception;
use GoodPhp\Serialization\TypeAdapter\Exception\CollectionItemMappingException;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Property\BoundClassProperty;
use RuntimeException;
use Throwable;

class PropertyMappingException extends RuntimeException
{
	public function __construct(
		public readonly string $path,
		Throwable              $previous,
	)
	{
		parent::__construct("Could not map property at path '{$path}': {$previous->getMessage()}", 0, $previous);
	}

	public static function rethrow(BoundClassProperty|string $serializedName, callable $callback): mixed
	{
		$serializedName = $serializedName instanceof BoundClassProperty ? $serializedName->serializedName() : $serializedName;

		try {
			return $callback();
		} catch (PropertyMappingException $e) {
			throw new self($serializedName ? $serializedName . '.' . $e->path : $e->path, $e->getPrevious());
		} catch (CollectionItemMappingException $e) {
			throw new self($serializedName ? $serializedName . '.' . $e->key : $e->key, $e->getPrevious());
		} catch (Exception $e) {
			throw new self($serializedName, $e);
		}
	}
}
