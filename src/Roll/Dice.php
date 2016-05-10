<?php
/**
 * Created by PhpStorm.
 * User: Sean
 * Date: 2016.05.08
 * Time: 00:07
 */

namespace CybernetAPI\Roll;

use CybernetAPI\AbstractRoute as Route;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Dice extends Route
{
    protected $rule = null;
    protected $rules = [];
    protected $roll = [];
    protected $sides = 20;
    protected $pool = 1;

    public function __invoke(Request $request, Response $response)
    {
        $pattern = $request->getAttribute('pattern', '1d20');
        $this->rule = $request->getAttribute('rule');
        //TODO: For future implementation of multiple rules.
        //$this->rules =  explode('/', $request->getAttribute('rules'));

        if (!stristr($pattern, 'd')) {
            $this->container->logger->error("Bad Pattern: " . $pattern);
            throw new \Exception('Bad pattern given');
        }
        list($this->pool, $this->sides) = explode('d', $pattern);

        //TODO: Add more error checking against $pool and $sides being integers?
        $this->pool = ($this->pool !== '' ? (int)$this->pool : 1);
        $this->sides = ($this->sides !== '' ? (int)$this->sides : 20);

        $this->roll['total'] = 0;
        $this->roll['rolls'] = [];

        for ($rollNum = 1; $rollNum <= $this->pool; $rollNum++) {
            $currRoll = $this->roll();
            $this->roll['rolls'][] = $currRoll;
        }

        rsort($this->roll['rolls']);

        switch ($this->rule) {
            case 'rak':
                $this->rak();
                break;
            default:

        }



        $this->roll['total'] = array_sum($this->roll['rolls']);

        $jsonResponse = $response->withJson($this->roll);

        return $jsonResponse;
    }

    protected function roll()
    {
        return rand(1, $this->sides);
    }

    protected function rak()
    {
        if ($this->pool <= 1) {
            throw new \Exception('RAK requires a pool greater than 1.');
        } else {
            $this->roll['throwaway'] = array_pop($this->roll['rolls']);
        }
    }
}
