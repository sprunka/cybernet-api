<?php

namespace CybernetAPI\Roll;

use CybernetAPI\AbstractRoute as Route;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class Dice
 * @package CybernetAPI\Roll
 */
class Dice extends Route
{
    /**
     * @var null
     */
    protected $rule = null;
    /**
     * @var array
     */
    protected $rules = [];
    /**
     * @var array
     */
    protected $roll = [];
    /**
     * @var int
     */
    protected $sides = 20;
    /**
     * @var int
     */
    protected $pool = 1;
    /**
     * @var int
     */
    protected $bonus = 0;

    /**
     * @param Request $request
     * @param Response $response
     * @return string JSON Encoded
     * @throws \Exception
     */
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

        if (stristr($tempSides, ' ')) {
            list($this->sides, $this->bonus) = explode(' ', $tempSides);
        } elseif (stristr($tempSides, '-')) {
            list($this->sides, $this->bonus) = explode('-', $tempSides);
            $this->bonus = -(int)$this->bonus;
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
                $this->rollAndDropLowestDice($drop);
                break;
            case 'rr1s':
            case 'rr2s':
                $numberToReRoll = substr($this->rule, -2, 1);
                if ($this->sides <= $numberToReRoll) {
                    $this->container->logger->error('To use ReRoll ' . $numberToReRoll . 's the number of sides must be greater than ' . $numberToReRoll . '.');
                    throw new \Exception('To use ReRoll ' . $numberToReRoll . 's the number of sides must be greater than ' . $numberToReRoll . '.');
                }
                $this->rerollThresholdAndBelow($numberToReRoll);
                break;
            case 'sort':
                $this->basicRoll();
                rsort($this->roll['rolls']);
                break;
            default:
                $this->basicRoll();
        }
        $this->roll['total'] = array_sum($this->roll['rolls']) + $this->bonus;
        $jsonResponse = $response->withJson($this->roll,200);
        return $jsonResponse;
    }

    /**
     * @param int $numberToReRoll
     */
    protected function rerollThresholdAndBelow(int $numberToReRoll)
    {
        for ($rollNum = 1; $rollNum <= $this->pool; $rollNum++) {
            $currRoll = $this->getNewRoll(true, $numberToReRoll);
            $this->roll['rolls'][] = $currRoll;
        }
    }

    protected function basicRoll()
    {
        for ($rollNum = 1; $rollNum <= $this->pool; $rollNum++) {
            $currRoll = $this->getNewRoll();
            $this->roll['rolls'][] = $currRoll;
        }
    }

    /**
     * @param bool $doReRoll
     * @param int $numberToReRoll
     * @return int
     */
    protected function getNewRoll(bool $doReRoll = false, int $numberToReRoll = 0)
    {
        $currentRoll = rand(1, $this->sides);
        if ($doReRoll && $currentRoll <= $numberToReRoll) {
            $currentRoll = $this->getNewRoll($doReRoll, $numberToReRoll);
        }
        return $currentRoll;
    }

    /**
     * @param int $drop
     */
    protected function rollAndDropLowestDice(int $drop)
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
}
