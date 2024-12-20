<?php

namespace dnk;

class Lang extends dnk
{
	public static function isArray($value)
	{
		return \is_array($value);
	}
	public static function isBoolean($value)
	{
		return \is_bool($value);
	}
	public static function isCallable($value)
	{
		return \is_callable($value);
	}
	public static function isCountable($value)
	{
		return \is_countable($value);
	}
	public static function isDouble($value)
	{
		return \is_double($value);
	}
	public static function isFinite($value)
	{
		if (!self::isNumber($value)) return false;
		$value = \floatval($value);
		return \is_finite($value);
	}
	public static function isFloat($value)
	{
		return \is_float($value);
	}
	public static function isId($value)
	{
		return \is_int($value) && $value > 0;
	}
	public static function isInt($value)
	{
		return \is_int($value);
	}
	public static function isInteger($value)
	{
		return \is_integer($value);
	}
	public static function isIterable($value)
	{
		return \is_iterable($value);
	}
	public static function isLong($value)
	{
		return \is_long($value);
	}
	public static function isNull($value)
	{
		return \is_null($value);
	}
	public static function isNumber($value)
	{
		switch (\gettype($value)) {
			case 'integer':
			case 'double':
				return true;
			default:
				return false;
		}
	}
	public static function isNumeric($value)
	{
		return \is_numeric($value);
	}
	public static function isObject($value)
	{
		return \is_object($value);
	}
	public static function isRegExp($value)
	{
		return @\preg_match($value, '') === false ? false : true;
	}
	public static function isResource($value)
	{
		return \is_resource($value);
	}
	public static function isScalar($value)
	{
		return \is_scalar($value);
	}
	public static function isString($value)
	{
		return \is_string($value);
	}
	public static function isLength($value)
	{
		return \gettype($value) === 'integer'
			&& $value > -1
			&& $value <= PHP_INT_MAX;
	}
	public static function isTraversable($value)
	{
		return $value instanceof \Traversable;
	}

	public static function toBoolean($value)
	{
		return \boolval($value);
	}

	public static function toFloat($value)
	{
		return (float) self::toNumber($value);
	}

	public static function toId($value)
	{
		if (\is_string($value) && \ctype_digit($value))
		{
			$value = \intval($value);
		}
		return self::isId($value) ? $value : null;
	}

	public static function toInteger($value)
	{
		return \intval(self::toNumber($value));
	}

	public static function toNumber($value)
	{
		if (!$value) return 0;
		if (\is_int($value) || \is_float($value)) return $value;
		$value = \preg_replace(
			'/[^\d\.]/',
			'',
			\strval($value)
		);
		return \str_contains($value, '.') ? \floatval($value) : \intval($value);
	}

	public static function toString($value)
	{
		return \strval($value);
	}

	public static function __callStatic($name, $arguments)
	{
		if (\method_exists(self::class, $name))
		{
			return self::$name(...$arguments);
		}

		return parent::__callStatic($name, $arguments);
	}
}