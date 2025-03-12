# Good PHP serialization

The concept is similar to Moshi, a Java/Kotlin serialization library - the least effort
without sacrificing customizability, support for different formats or ease of use.

This is what it can serialize and deserialize out-of-the-box:

```php
/**
 * @template T1
 */
class Item
{
	/**
	 * @param BackedEnumStub[] $array
	 * @param Collection<int, T1>
	 * @param T1 $generic
	 * @param NestedGeneric<int, T1> $nested
	 */
	public function __construct(
		// Scalars
		public readonly int $int,
		public readonly float $float,
		public readonly string $string,
		public readonly bool $bool,
		// Nullable and optional values
		public readonly ?string $nullableString,
		public readonly int|null|MissingValue $optional,
		// Custom property names
		#[SerializedName('two')] public readonly string $one,
		// Backed enums
		public readonly BackedEnumStub $backedEnum,
		// Generics and nested objects
		public readonly mixed $generic,
		public readonly NestedGenerics $nestedGeneric,
		// Arrays and Illuminate Collection of any type (with generics!)
		public readonly array $array,
		public readonly Collection $collection,
		// Dates
		public readonly DateTime $dateTime,
		public readonly Carbon $carbon,
	) {}
}
```

You can then convert it into a "primitive" (scalars and arrays of scalars) or JSON:

```php
$primitiveAdapter = $serializer->adapter(
	PrimitiveTypeAdapter::class, 
	NamedType::wrap(Item::class, [Carbon::class])
);
// -> ['int' => 123, ...]
$primitiveAdapter->serialize(new Item(...));

$jsonAdapter = $serializer->adapter(
	JsonTypeAdapter::class, 
	NamedType::wrap(Item::class, [PrimitiveType::int()])
);
// new Item(123, ...)
$jsonAdapter->deserialize('{"int": 123, ...}');
```

## Documentation

Basic documentation is available in [docs/](docs). For examples, you can look at the
test suite: [tests/Integration](tests/Integration).

## Why this over everything else?

There are some alternatives to this, but they usually lack one of the following:

- stupid simple internal structure: no node tree, no value/JSON wrappers, no in-repo custom reflection implementation, no PHP parsing
- doesn't rely on inheritance of serializable classes, hence allows serializing third-party classes
- parses existing PHPDoc information instead of duplicating it through attributes
- supports generic types which are quite useful for wrapper types
- allows simple extension through mappers and complex stuff through type adapters
- produces developer-friendly error messages for invalid data
- correctly handles optional (missing keys) and `null` values as separate concerns
- simple to extend with additional formats
