<?php


namespace Parsley\Utils;

use Illuminate\Container\Container;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;


class Locator
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getSuffix()
    {
        return 'Task';
    }

    public function getBaseClass()
    {
        return 'Parsley\\Task';
    }

    public function register($name, $fq_class_name)
    {

    }

    public function discover($location, $ns = '')
    {
        // Assume we have PSR autoloadable classes

        $suffix = $this->getSuffix() . '.php';

        $pattern = "/{$suffix}$/";

        // NOTE: we are loading only valid for PHP namespace files and directories
        $dir   = new RecursiveDirectoryIterator($location);
        $ite   = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);

        $classes = array();

        $ns = trim($ns, '\\');

        if (!empty($ns)) {
            $ns = $ns . '\\';
        }

        foreach ($files as $file) {
            $path  = $file[0];
            $class = substr($path, strlen($location) + 1);
            $class = str_replace(DIRECTORY_SEPARATOR, '\\', $class);
            $class = $ns . preg_replace($suffix, '', $class);

            if (!class_exists($class)) {
                $this->container['events']->fire('parsley.locator.error.nonexistent', array($class, $path));
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract()) {
                $this->container['events']->fire('parsley.locator.error.abstract', array($class, $path));
                continue;
            }

            if (!$reflection->isSubclassOf($this->getBaseClass())) {
                $this->container['events']->fire('parsley.locator.error.notsubclass', array($class, $path, $this->getBaseClass()));
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }


}