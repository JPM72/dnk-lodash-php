<?php

namespace dnk;

class Math extends dnk
{
	/**
	 * @return int|float
	 */
	public static function add($x, $y)
	{
		return $x + $y;
	}

	public static function inRange(float $number, float $start = 0, float $end = 0): bool
	{
		if (0.0 === $end)
		{
			$end = $start;
			$start = 0;
		}

		return $number >= \min($start, $end) && $number < \max($start, $end);
	}

	/**
	 * @param int|float $number
	 * @param ?int $precision
	 * @return int|float
	 */
	public static function round($number, ?int $precision = 0)
	{
		return \round($number, $precision);
	}

	public static function sum(array $array)
	{
		return \array_sum($array);
	}

	public static function sumBy(array $array, $iteratee)
	{
		$a = Coll::map($array, $iteratee);
		return self::sum($a);
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
