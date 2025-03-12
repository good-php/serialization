## Error handling

This is expected to be used with client-provided data, so good error descriptions is a must.
These are some of the errors you'll get:

- Expected value of type 'int', but got 'string'
- Expected value of type 'string', but got 'NULL'
- Failed to parse time string (2020 dasd) at position 5 (d): The timezone could not be found in the database
- Expected value of type 'string|int', but got 'boolean'
- Expected one of [one, two], but got 'five'
- Could not map item at key '1': Expected value of type 'string', but got 'NULL'
- Could not map item at key '0': Expected value of type 'string', but got 'NULL' (and 1 more errors)."
- Could not map property at path 'nested.field': Expected value of type 'string', but got 'integer'

All of these are just a chain of PHP exceptions with `previous` exceptions. Besides
those messages, you have all the thrown exceptions with necessary information.
