<?php
/**
 * Created by PhpStorm.
 * User: Sean
 * Date: 2016.05.08
 * Time: 22:12
 */

namespace CybernetAPI;


class AbstractRoute
{
    public function __construct($container)
    {
        $this->container = $container;
    }
}
