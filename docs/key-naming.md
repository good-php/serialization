# Naming of keys

By default, serializer preserves the naming of keys, but this is easily customizable (in order of priority):

- specify a custom property name using the `#[SerializedName]` attribute
- specify a custom naming strategy per class using the `#[SerializedName]` attribute
- specify a custom global (default) naming strategy (use one of the built-in or write your own)

Here's an example:

```php
(new SerializerBuilder())->namingStrategy(BuiltInNamingStrategy::SNAKE_CASE);

// Uses snake_case by default
class Item1 {
	public function __construct(
		public int $keyName, // appears as "key_name" in serialized data
		#[SerializedName('second_key')] public int $firstKey, // second_key
		#[SerializedName(BuiltInNamingStrategy::PASCAL_CASE)] public int $thirdKey, // THIRD_KEY
	) {}
}

// Uses PASCAL_CASE by default
#[SerializedName(BuiltInNamingStrategy::PASCAL_CASE)]
class Item2 {
	public function __construct(
		public int $keyName, // KEY_NAME
	) {}
}
```

Out of the box, strategies for `snake_case`, `camelCase` and `PascalCase` are
available in `BuiltInNamingStrategy`, but it's trivial to implement your own:

```php
class PrefixedNaming implements NamingStrategy {
	public function __construct(
		private readonly string $prefix,
	) {}
	
	public function translate(PropertyReflection $property): string
	{
		return $this->prefix . $property->name();
	}
}

#[SerializedName(new PrefixedNaming('$'))]
class SiftTrackData {}
```
