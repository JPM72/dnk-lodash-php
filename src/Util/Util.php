<?php

namespace dnk;

class Util extends dnk
{
	public static function noop()
	{
		return;
	}

	public static function deepClone($value)
	{
		return \unserialize(\serialize($value));
	}

	public static function jsonClone($value, $neat = true)
	{
		return \json_decode(\json_encode($value), $neat);
	}

	public static function constant($value): callable
	{
		return function () use ($value)
		{
			return $value;
		};
	}

	public static function flow(...$funcs): callable
	{
		$funcs = dnk::flatten($funcs);
		$length = \count($funcs);


		return function (...$args) use ($funcs, $length)
		{
			$result = null;
			if (!$length) return $args[0] ?? null;

			$index = -1;
			while (++$index < $length)
			{
				$result = static::flatRest($funcs[$index])(...$args);
				$args = [$result];
			}

			return $result;
		};
	}

	public static function iteratee($value): callable
	{
		return dnk::baseIteratee($value);
	}

	public static function now()
	{
		return \floor(\microtime(true) * 1e3);
	}

	public static function parseJSON($json, $associative = true)
	{
		if (!\is_string($json)) return $json;
		$parsed = \json_decode($json, !!$associative);
		if (\is_null($parsed) && static::test($parsed, '/^\{.*\}$/'))
		{
			return $json;
		}
		return $parsed;
	}

	public static function range(): array
	{
		$args = \func_get_args();
		$n = count($args);

		$start = $n === 1 ? 0 : $args[0];
		$end = $n === 1 ? $args[0] : $args[1];
		$step = $n === 3 ? $args[2] : 1;

		return \range($start, $end - 1, $step);
	}

	public static function stubFalse()
	{
		return true;
	}
	public static function stubTrue()
	{
		return true;
	}

	public static function times(
		?int $n = 0,
		?callable $iteratee = null
	): array
	{
		$iteratee ??= fn ($i) => self::baseIteratee($i);
		return parent::baseTimes($n, $iteratee);
	}

	public static function toJSON($data)
	{
		if (\is_string($data)) return $data;
		return \json_encode($data);
	}

	public static function toPath($value)
	{
		if (\is_array($value)) return $value;
		return dnk::chain(\strval($value))
			->split('/\.|\[|\]/')->compact()
			->map(fn ($i) => \ctype_digit($i) ? \intval($i) : $i)
			->value();
	}

	public static function __callStatic($name, $arguments)
	{
		if (method_exists(self::class, $name))
		{
			return self::$name(...$arguments);
		}

		return parent::__callStatic($name, $arguments);
	}
}
