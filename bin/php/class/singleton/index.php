<?php
namespace singleton;
abstract class index {
    private static $instances = array( );
    protected function __construct( ) { }
    public static function getInstance( ) {
        $class = get_called_class( );
        if (!isset( self::$instances[ $class ])) {
            self::$instances[ $class ] = new $class( );
        }
        return self::$instances[ $class ];
    }
    private function __clone( ) { }
    private function __wakeup( ) { }
} ?>
