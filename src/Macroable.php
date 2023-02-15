<?php

namespace App;

trait Macroable
{
	/**
	 * @param object $context
	 * @param string $name
	 * @param array $args
	 * @return $this|mixed
	 */
	public static function callContext(object $context, string $name, array $args)
	{
		// Call method from context
		if (method_exists($context, $name)) {
			return $context->$name(...$args);
		}

		// Set property directly (if exists)
		if (property_exists($context, $name)) {
			$context->{$name} = current($args);
		}

		return $context;
	}

	/**
	 * Call dynamic method
	 *
	 * @param string $name
	 * @param array $args
	 * @return DB
	 */
	public function __call(string $name, array $args)
	{
		return self::callContext($this, $name, $args);
	}

	/**
	 * Call dynamic static method
	 *
	 * @param string $name
	 * @param array $args
	 * @return DB
	 */
	public static function __callStatic(string $name, array $args)
	{
		return self::callContext(new static, $name, $args);
	}
}