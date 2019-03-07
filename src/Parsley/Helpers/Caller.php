<?php


namespace Parsley\Helpers;


use Parsley\Helpers\Exceptions\CallerException;

class Caller
{
    public static function unpackArguments(array $arguments, array $order)
    {
        if (empty($arguments) and empty($order)) {
            return $arguments;
        }

        $given_keys = array_keys($arguments);
        $not_given  = array_diff($order, $given_keys);

        if (!empty($not_given)) {
            $missed_arguments = implode(', ', $not_given);
            throw new CallerException("Some arguments missed ({$missed_arguments})");
        }

        $extra_given = array_diff($given_keys, $order);

        if (!empty($extra_given)) {
            $extra_arguments = implode(', ', $extra_given);
            throw new CallerException("Extra arguments given ({$extra_arguments})");
        }

        $args = array();

        foreach ($order as $name) {
            $args[$name] = $arguments[$name];
        }

        return $args;
    }

    public static function packArguments(array $arguments, array $order)
    {
        if (empty($arguments) and empty($order)) {
            return $arguments;
        }

        if (count($arguments) != count($order)) {
            throw new CallerException("Arguments number doesn't match preset one");
        }

        $arguments = array_combine($order, $arguments);

        return $arguments;
    }

    public static function call($instance, $method, array $arguments = array())
    {
        $args = array_values($arguments);

        switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array(array($instance, $method), $args);
        }
    }
} 