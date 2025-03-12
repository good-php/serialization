# Adding more formats

Different formats are supported through "type adapter types". These are just interfaces, but their class
name is passed to all type adapter factories and type mappers. For example, this is how you could implement
basic XML support that uses all of the existing adapters that target "primitive" format:

```php
interface XmlTypeAdapter extends TypeAdapter {}

final class FromPrimitiveXmlTypeAdapter implements XmlTypeAdapter
{
	public function __construct(
		private readonly PrimitiveTypeAdapter $primitiveAdapter,
	) {
	}

	public function serialize(mixed $value): mixed
	{
		return xml_encode($this->primitiveAdapter->serialize($value));
	}

	public function deserialize(mixed $value): mixed
	{
		return $this->primitiveAdapter->deserialize(xml_decode($value));
	}
}
```
