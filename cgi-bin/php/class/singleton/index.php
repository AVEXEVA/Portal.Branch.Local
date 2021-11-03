<?php
NAMESPACE SINGLETON;
CLASS INDEX {
    PRIVATE STATIC $INSTANCES = [];
    PROTECTED FUNCTION __construct() { }
    PROTECTED FUNCTION __clone() { }
    PUBLIC FUNCTION __wakeup(){throw new \Exception("Cannot unserialize a singleton.");}
    PUBLIC STATIC FUNCTION getInstance(): Singleton{
        $CLS = static::class;
        if (!isset(self::$INSTANCES[ $CLS ])) {self::$INSTANCES[ $CLS ] = new static();}
        return self::$INSTANCES[ $CLS ];
    }
}?>
