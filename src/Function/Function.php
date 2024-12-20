<?php

namespace dnk;

class Functions extends dnk
{
	private const HOT_COUNT = 800;
	private const HOT_SPAN = 16;

	public static function ary(callable $func, int $arity): callable
	{
		return function (...$args) use ($func, $arity)
		{
			\array_splice($args, $arity);
			return $func(...$args);
		};
	}
	public static function unary(callable $func): callable
	{
		return self::ary($func, 1);
	}

	protected static function shortOut(callable $func): callable
	{
		$count = 0;
		$lastCalled = 0;

		return function () use ($func, &$count, &$lastCalled)
		{
			$stamp = microtime(true);
			$remaining = self::HOT_SPAN - ($stamp - $lastCalled);

			$lastCalled = $stamp;
			if ($remaining > 0)
			{
				if (++$count >= self::HOT_COUNT)
				{
					return func_get_arg(0);
				}
			}
			else
			{
				$count = 0;
			}

			return $func(...func_get_args());
		};
	}

	public static function before(int $n, callable $func): callable
	{
		$result = null;

		return function (...$args) use (&$result, &$n, &$func)
		{
			if (--$n > 0)
			{
				$result = $func(...$args);
			}

			return $result;
		};
	}

	public static function negate(callable $predicate): callable
	{
		return function () use ($predicate)
		{
			return !$predicate(...\func_get_args());
		};
	}

	public static function once(callable $func): callable
	{
		return static::before(2, $func);
	}

	public static function _rest(callable $func, $start = null): callable
	{
		return static::overRest($func, $start, 'dnk\identity');
	}

	public static function overRest(
		callable $func,
		$start,
		callable $transform
	): callable
	{
		$parameters = (new \ReflectionFunction($func))->getNumberOfParameters();
		$start = \max($start ?? $parameters - 1, 0);

		return function () use ($func, $start, $transform)
		{
			$args = \func_get_args();
			$index = -1;
			$length = \max(\count($args) - $start, 0);
			$array = [];

			while (++$index < $length)
			{
				$array[$index] = $args[$start + $index];
			}
			$index = -1;
			$otherArgs = [];
			while (++$index < $start)
			{
				$otherArgs[$index] = $args[$index];
			}
			$otherArgs[$start] = $transform($array);

			return $func(...$otherArgs);
		};
	}

	public static function flatRest(callable $func): callable
	{
		return static::shortOut(
			static::overRest($func, null, 'dnk\Arr::flatten')
		);
	}

	public static function rest(callable $func, ?int $start = null): callable
	{
		return static::baseRest($func, $start);
	}

	public static function spread(callable $func, ?int $start = null): callable
	{
		$start = null === $start ? 0 : \max($start, 0);

		return static::baseRest(function ($args) use ($start, $func)
		{
			$array = $args[$start];
			$otherArgs = static::castSlice($args, 0, $start);

			if ($array)
			{
				$otherArgs = \array_merge($otherArgs, $array);
			}

			return $func(...$otherArgs);
		});
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
