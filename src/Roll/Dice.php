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
    protected $bonus = 0;

    public function __invoke(Request $request, Response $response)
    {
        $pattern = strtolower($request->getAttribute('pattern', '1d20'));
        $this->rule = strtolower($request->getAttribute('rule'));
        //TODO: For future implementation of multiple rules.
        //$this->rules =  explode('/', $request->getAttribute('rules'));

        if (!stristr($pattern, 'd')) {
            $this->container->logger->error("Bad Pattern: " . $pattern);
            throw new \Exception('Bad pattern given');
        }
        list($this->pool, $tempSides) = explode('d', $pattern);

        if (stristr($tempSides,' ')) {
            list($this->sides, $this->bonus) = explode(' ',$tempSides);
        } elseif (stristr($tempSides,'-')) {
            list($this->sides, $this->bonus) = explode('-',$tempSides);
            $this->bonus = - (int) $this->bonus;
        } else {
            $this->sides = $tempSides;
        }



        //TODO: Add more error checking against $pool and $sides being integers?
        $this->pool = ($this->pool !== '' ? (int)$this->pool : 1);
        $this->sides = ($this->sides !== '' ? (int)$this->sides : 20);

        $this->roll['total'] = 0;
        $this->roll['rolls'] = [];

        switch ($this->rule) {
            case 'rad1':
            case 'rad2':
                $drop = substr($this->rule, -1, 1);
                if ($this->pool <= $drop) {
                    $this->container->logger->error('Roll and Drop ' . $drop . ' requires a pool greater than ' . $drop);
                    throw new \Exception('Roll and Drop ' . $drop . ' requires a pool greater than ' . $drop);
                }
                $this->basicRoll();
                $this->rad($drop);
                break;
            case 'rr1s':
            case 'rr2s':
                $drop = substr($this->rule, -2, 1);
                if ($this->sides <= $drop) {
                    $this->container->logger->error('To use ReRoll ' . $drop . 's the number of sides must be greater than ' . $drop . '.');
                    throw new \Exception('To use ReRoll ' . $drop . 's the number of sides must be greater than ' . $drop . '.');
                }
                $this->basicRoll(true, $drop);
                break;
            case 'sort':
                $this->basicRoll();
                rsort($this->roll['rolls']);
                break;
            default:
                $this->basicRoll();
        }

        $this->roll['total'] = array_sum($this->roll['rolls']) + $this->bonus;

        $jsonResponse = $response->withJson($this->roll);

        return $jsonResponse;
    }

    protected function roll($reRoll = false, $minRoll = 1)
    {
        $currentRoll = rand(1, $this->sides);
        if ($reRoll && $currentRoll <= $minRoll) {
            $currentRoll = $this->roll($reRoll, $minRoll);
        }
        return $currentRoll;

    }

    protected function rad($drop = 1)
    {
        for ($drops = 0; $drops < $drop; $drops++) {
            $lowestRoll = min($this->roll['rolls']);
            $trash = array_keys($this->roll['rolls'], $lowestRoll);
            $trashKey = array_shift($trash);
            $this->roll['throwaway'][] = $this->roll['rolls'][$trashKey];
            unset($this->roll['rolls'][$trashKey]);
            $this->roll['rolls'] = array_merge($this->roll['rolls']);
        }
    }

    protected function basicRoll($reRoll = false, $minRoll = 1)
    {
        for ($rollNum = 1; $rollNum <= $this->pool; $rollNum++) {
            $currRoll = $this->roll($reRoll, $minRoll);
            $this->roll['rolls'][] = $currRoll;
        }
    }
}
