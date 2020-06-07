<?php

/**
 *
 */

/**
 * Undocumented function
 *
 * @param [type] $class
 * @param array $attributes
 * @param [type] $times
 * @return void
 */
function create($class, $attributes = [], $times = null)
{
    return factory("App\\Models\\{$class}", $times)->create($attributes);
}

/**
 * Undocumented function
 *
 * @param [type] $class
 * @param array $attributes
 * @param [type] $times
 * @return void
 */
function make($class, $attributes = [], $times = null)
{
    return factory("App\\Models\\{$class}", $times)->make($attributes);
}

/**
 * Undocumented function
 *
 * @param [type] $class
 * @param array $attributes
 * @param [type] $times
 * @return void
 */
function raw($class, $attributes = [], $times = null)
{
    return factory("App\\Models\\{$class}", $times)->raw($attributes);
}
