<?php
/**
 * Created by PhpStorm.
 * User: Sean
 * Date: 2016.05.08
 * Time: 00:07
 */

namespace CybernetAPI\Roll;

use CybernetAPI\AbsrtactRoute as Route;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Dice extends Route
{
    public function __invoke(Request $request, Response $response)
    {
        $pattern = $request->getAttribute('pattern', '1d20');
        $rule = $request->getAttribute('rule');

        if (!stristr($pattern, 'd')) {
            $this->container->logger->error("Bad Pattern: " . $pattern);
            throw new \Exception('Bad pattern given');
        }

        list($pool, $sides) = explode('d', $pattern);

        //TODO: Add more error checking against $pool and $sides being integers?
        $pool = ($pool !== '' ? (int)$pool : 1);
        $sides = ($sides !== '' ? (int)$sides : 20);
        $roll = [];
        $roll['total'] = 0;
        $roll['rolls'] = [];

        for ($rollNum = 1; $rollNum <= $pool; $rollNum++) {
            $currRoll = $this->roll($sides);
            $roll['rolls'][] = $currRoll;
            $roll['total'] += $currRoll;
        }

        $jsonResp = json_encode($roll);

        $response->getBody()->write($jsonResp);

        return $response;
    }

    protected function roll($sides)
    {
        return rand(1, $sides);
    }
}
