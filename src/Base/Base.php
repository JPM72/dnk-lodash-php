<?php

namespace dnk;

class dnk
{
	private $value;
	private $inChain = false;

	protected static function arrayPush(&$array, $values)
	{
		$index = -1;
		$length = \is_array($values) ? \count($values) : \strlen($values);
		$offset = \count($array);

		while (++$index < $length)
		{
			$array[$offset + $index] = $values[$index];
		}

		return $array;
	}
	protected static function arrayIncludes(?array $array, $value)
	{
		return null !== $array && self::indexOf($array, $value, 0) > -1;
	}
	protected static function arrayIncludesWith(?array $array, $value, callable $comparator)
	{
		$array = $array ?? [];

		foreach ($array as $v)
		{
			if ($comparator($value, $v))
			{
				return true;
			}
		}

		return false;
	}
	protected static function arrayMap(?array $array, callable $iteratee)
	{
		$index = -1;
		$length = $array === null ? 0 : \count($array);
		$result = [];

		while (++$index < $length)
		{
			$result[$index] = $iteratee($array[$index], $index, $array);
		}

		return $result;
	}

	protected static function castSlice(array $array, int $start, ?int $end = null): array
	{
		$length = \count($array);
		$end = null === $end ? $length : $end;

		return (!$start && $end >= $length) ? $array : \array_slice($array, $start, $end);
	}

	public static function getIdentityFunction()
	{
		return function ($value = null)
		{
			return $value;
		};
	}
	public static function identity($value = null)
	{
		return $value;
	}

	public static function defaultTo($value, $defaultValue)
	{
		return ($value !== null && (\is_object($value) || !\is_nan(\floatval($value)))
		) ? $value : $defaultValue;
	}

	public static function basePredicate()
	{
		return function ($x)
		{
			return \boolval($x);
		};
	}

	public static function baseIteratee($value = null): callable
	{
		if (\is_callable($value))
		{
			return $value;
		}

		if (\is_bool($value))
		{
			return fn ($v) => $v === $value;
		}

		if ($value === null)
		{
			return self::getIdentityFunction();
		}

		if (!\is_array($value)) return Propper::property($value);

		if (
			\count($value) === 2
			&&
			\array_keys($value) === [0, 1]
		)
		{
			return Propper::matches($value[0], $value[1]);
		}

		return Propper::matches($value);
	}

	protected static function overRest(callable $func, $start, callable $transform): callable
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

	protected static function baseTimes(int $n, callable $iteratee)
	{
		$index = -1;
		$result = [];

		while (++$index < $n)
		{
			$result[$index] = $iteratee($index);
		}

		return $result;
	}

	protected static function baseProperty($key)
	{
		return function ($object) use ($key)
		{
			return null === $object ? null : $object[$key];
		};
	}

	protected static function baseRest(callable $func, $start = null): callable
	{
		return self::overRest($func, $start, fn ($v) => self::identity($v));
	}

	public static function isEqual($x, $y): bool
	{
		return $x === $y;
	}

	public static function eq($x, $y): bool
	{
		return self::isEqual($x, $y);
	}

	public static function chain($value): dnk
	{
		$result = _dnk($value);
		$result->chainIn();
		return $result;
	}

	protected function chainIn()
	{
		$this->inChain = true;
		return $this;
	}

	public function __construct($value = null)
	{
		$this->value = $value;
	}

	public function tap($interceptor, ...$args)
	{
		$interceptor($this->value, ...$args);

		return $this;
	}

	public function thru(callable $interceptor, ...$args)
	{
		$this->value = $interceptor($this->value, ...$args);
		return $this;
	}

	public function value()
	{
		$this->inChain = false;
		return $this->value;
	}

	static private $subClasses = [
		'Coll', 'Arr', 'Propper', 'KeyPath', 'Functions', 'Str',
		'Math', 'Util', 'Lang',
	];

	private static $methodCache = [];

	private static function getSignature($class, $methodName)
	{
		return "dnk\\" . $class . '::' . $methodName;
	}

	private static function cacheMethodCall($signature)
	{
		$callable = function (...$args) use ($signature)
		{
			return $signature(...$args);
		};
		self::$methodCache[$signature] = $callable;
		return $callable;
	}

	public static function __callStatic($name, $arguments)
	{

		if (\method_exists(self::class, $name))
		{
			return self::$name(...$arguments);
		}

		foreach (self::$subClasses as $class)
		{
			$className = __NAMESPACE__ . "\\" . $class;

			$signature = self::getSignature($class, $name);

			if (isset(self::$methodCache[$signature]))
			{
				return self::$methodCache[$signature](...$arguments);
			}

			if (\method_exists($className, $name))
			{
				return self::cacheMethodCall($signature)(...$arguments);
			}
			continue;
		}

		throw new \BadMethodCallException("Call to undefined method $name");
	}

	public function __call($method, $arguments)
	{
		$this->value = self::__callStatic($method, \array_merge([$this->value], $arguments));

		return $this;
	}
}
function _dnk()
{
	return new dnk(...func_get_args());
}

if (!defined('dnk'))
{
	define('dnk', dnk::class);
}
