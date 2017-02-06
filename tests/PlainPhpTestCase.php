<?php

abstract class PlainPhpTestCase extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        \Konsulting\Laravel\load_collection_extensions();
    }
}
