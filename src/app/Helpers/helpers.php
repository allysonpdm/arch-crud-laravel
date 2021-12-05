<?php

if(!function_exists('bootUp')){
    /**
     *
     */
    function bootUp(array $classes)
    {
        foreach ($classes as $class) {
            if (class_exists($class))
                return new $class;
        }
        return new stdClass;
    }
}
