<?php

namespace GoodPhp\Serialization\TypeAdapter\Json;

use GoodPhp\Serialization\TypeAdapter\TypeAdapter;

/**
 * @template T Type being serialized
 *
 * @extends TypeAdapter<T, string>
 */
interface JsonTypeAdapter extends TypeAdapter
{
}
