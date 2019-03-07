<?php


namespace Parsley\Helpers;


class Namer // not an APC
{
    public static function getCanonicalName($object, $redundant_prefix = '')
    {
        if (is_object($object)) {
            $name = get_class($object);
        } else {
            $name = $object;
        }

        // remove Task part from class name
        if ($redundant_prefix && $redundant_prefix == substr($name, -strlen($redundant_prefix))) {
            $name = substr($name, 0, strlen($name) - strlen($redundant_prefix));
            $name = rtrim($name, '\\');
        }

        $name = str_replace('\\', '.', $name);

        return $name;
    }
}