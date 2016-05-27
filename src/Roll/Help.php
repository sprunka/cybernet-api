<?php

namespace CybernetAPI\Roll;

use CybernetAPI\AbstractRoute as Route;
use Slim\Http\Request as Request;
use Slim\Http\Response as Response;

/**
 * Class Help
 * @package CybernetAPI\Roll
 */
class Help extends Route
{
    protected $help = ['path'=>'/roll/help/{subject}', 'description'=>'Get help with $subject in the Rolling category', 'options'=>['subject'=>['dice','help']]];
    /**
     * @param Request $request
     * @param Response $response
     * @return string JSON Encoded
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response)
    {
        try {
            $what = 'CybernetAPI\\Roll\\' . ucfirst($request->getAttribute('what', 'help'));

            $help = (new $what($this->container))->getHelp();

            $jsonResponse = $response->withJson($help, 200);
            return $jsonResponse;
        } catch (\Exception $e){
            $message = 'Failed loading help for Class: ' . $what;
            $this->container->logger->error($message);
            $jsonResponse = $response->withJson($e, 500);
            return $jsonResponse;
        }
    }
}
