<?php

interface MethodChainable
{
	/**
	 * This method invokes `interceptor` and returns value.
	 * The interceptor is invoked with one argument; (`value`).
	 * The purpose of this method is to "tap into" a
	 * method chain sequence in order to modify intermediate results.
	 *
	 * @param  callable $interceptor The function to invoke.
	 * @param  mixed   ...$args Optional. The arguments to provide to `interceptor`.
	 * @return self
	 */
	public function tap(callable $interceptor, ...$args): self;

	/**
	 * This method is like `tap` except that it returns the result of `interceptor`.
	 * The purpose of this method is to "pass thru" values
	 * replacing intermediate results in a method chain sequence.
	 *
	 * @param  callable $interceptor The function to invoke.
	 * @param  mixed   ...$args Optional. The arguments to provide to `interceptor`.
	 * @return mixed Returns the result of `interceptor`.
	 */
    public function thru(callable $interceptor, ...$args);
}

trait MethodChaining
{
	/**
	 * This method invokes `interceptor` and returns value.
	 * The interceptor is invoked with one argument; (`value`).
	 * The purpose of this method is to "tap into" a
	 * method chain sequence in order to modify intermediate results.
	 *
	 * @param  string|callable $interceptor The function to invoke.
	 * @param  mixed   ...$args Optional. The arguments to provide to `interceptor`.
	 * @return self
	 */
	public function tap($interceptor, ...$args): self
	{
		if (is_string($interceptor))
		{
			$this->$interceptor(...$args);
		} else
		{
			$interceptor($this, ...$args);
		}

        return $this;
	}

	/**
	 * This method is like `tap` except that it returns the result of `interceptor`.
	 * The purpose of this method is to "pass thru" values
	 * replacing intermediate results in a method chain sequence.
	 *
	 * @param  callable $interceptor The function to invoke.
	 * @param  mixed   ...$args Optional. The arguments to provide to `interceptor`.
	 * @return mixed Returns the result of `interceptor`.
	 */
    public function thru(callable $interceptor, ...$args)
	{
		return $interceptor($this, ...$args);
	}
}