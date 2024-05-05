<?php

namespace AntCMS;

abstract class AbstractPlugin
{
    /**
     * All plugins must impliment a construct function to then register any hooks or routes
     */
    abstract public function __construct();

    /** @TODO */
    public function registerApiRoute()
    {

    }
}
