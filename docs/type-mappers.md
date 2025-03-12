# Type mappers

Mappers are the simplest form customizing serialization of types. All you have
to do is to mark a method with either `#[MapTo]` or `#[MapFrom]` attribute,
specify the type in question as first parameter or return type and the serializer
will handle the rest automatically. A single mapper may have as many map methods as you wish.

```php
final class DateTimeMapper
{
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function serialize(DateTime $value): string
	{
		return $value->format(DateTimeInterface::RFC3339_EXTENDED);
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function deserialize(string $value): DateTime
	{
		return new DateTime($value);
	}
}

$serializer = (new SerializerBuilder())
	->addMapperLast(new DateTimeMapper())
	->build();
```

With mappers, you can even handle complex types - such as generics or inheritance:

```php
final class ArrayMapper
{
	#[MapTo(PrimitiveTypeAdapter::class)]
	public function to(array $value, Type $type, Serializer $serializer): array
	{
		$itemAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, $type->arguments[1]);
		
		return array_map(fn ($item) => $itemAdapter->serialize($item), $value);
	}

	#[MapFrom(PrimitiveTypeAdapter::class)]
	public function from(array $value, Type $type, Serializer $serializer): array
	{
		$itemAdapter = $serializer->adapter(PrimitiveTypeAdapter::class, $type->arguments[1]);

		return array_map(fn ($item) => $itemAdapter->deserialize($item), $value);
	}
}

final class BackedEnumMapper
{
	#[MapTo(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(BackedEnum::class))]
	public function to(BackedEnum $value): string|int
	{
		return $value->value;
	}
	
	#[MapFrom(PrimitiveTypeAdapter::class, new BaseTypeAcceptedByAcceptanceStrategy(BackedEnum::class))]
	public function from(string|int $value, Type $type): BackedEnum
	{
		$enumClass = $type->name;
		
		return $enumClass::tryFrom($value);
	}
}
```
