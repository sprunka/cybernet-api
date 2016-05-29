<?php
namespace CybernetAPI;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class AbstractRoute
 * @package CybernetAPI
 */
abstract class AbstractRoute
{
    /**
     * @var array
     */
    protected $help = [];

    /**
     * AbstractRoute constructor.
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @return object
     */
    public function getHelp()
    {
        return (object)$this->help;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    abstract public function __invoke(Request $request, Response $response);
}
