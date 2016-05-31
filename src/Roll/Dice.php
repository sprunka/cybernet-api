<?php

namespace CybernetAPI\Roll;

use CybernetAPI\AbstractRoute as Route;
use Slim\Http\Request as Request;
use Slim\Http\Response as Response;

/**
 * Class Dice
 * @package CybernetAPI\Roll
 */
class Dice extends Route
{
    protected $help = [
        'path' => '/roll/{pattern}/{rule1}/{rule2}/.../{ruleX}',
        'pattern' => 'TODO: document pattern rules.'
    ];
    protected $priorities = [
        'rr1s' => 'Reroll 1s',
        'rr2s' => 'Reroll 1s and 2s',
        'rad1' => 'Roll and Drop lowest die',
        'rad2' => 'Roll and Drop lowest 2 dice',
        'rak3' => 'Roll and Keep highest 3 dice',
        'rsort' => 'Sorts the results, High to Low',
        'sort' => 'Sorts the results, Low to High'
    ];

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
     * @var \Faker\Generator
     */
    protected $faker;

    public function __construct($container)
    {
        parent::__construct($container);
        /** @var \Faker\Generator faker */
        $this->faker = $this->container->faker;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response (JSON Encoded Content)
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response)
    {
        $pattern = strtolower($request->getAttribute('pattern', '1d20'));
        $this->rules = explode('/', $request->getAttribute('rules'));

        $this->rollTheBones($pattern);

        $jsonResponse = $response->withJson($this->roll, 200);
        return $jsonResponse;
    }

    protected function basicRoll()
    {
        // Initialize the rolls set before starting to ensure a clean result.
        $this->roll['rolls'] = [];
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
        $currentRoll = $this->faker->numberBetween(1, $this->sides);

        if ($doReRoll && $currentRoll <= $numberToReRoll) {
            $currentRoll = $this->getNewRoll($doReRoll, $numberToReRoll);
        }
        return $currentRoll;
    }

    /**
     * @param int $numberToReRoll
     */
    protected function rerollThresholdAndBelow(int $numberToReRoll)
    {
        // Initialize the rolls set before starting to ensure a clean result.
        $this->roll['rolls'] = [];
        for ($rollNum = 1; $rollNum <= $this->pool; $rollNum++) {
            $currRoll = $this->getNewRoll(true, $numberToReRoll);
            $this->roll['rolls'][] = $currRoll;
        }
    }

    /**
     * @param int $drop
     */
    protected function rollAndDropLowestDice(int $drop)
    {
        // TODO: RaD seems to force a sort(). Fix? If possible?
        $keepers = $this->roll['rolls'];
        $trashers = [];


        for ($drops = 0; $drops < $drop; $drops++) {
            $lowestRoll = min($keepers);
            $trashKeys = array_keys($keepers, $lowestRoll);
            $trashKey = array_shift($trashKeys);
            $trashers[] = $keepers[$trashKey];
            unset($keepers[$trashKey]);
            $keepers = array_merge($keepers);
        }
        $this->roll['rolls'] = $keepers;
        if (!empty($this->roll['throwaway'])) {
            $this->roll['throwaway'] = array_merge($trashers, $this->roll['throwaway']);
        } else {
            $this->roll['throwaway'] = $trashers;
        }

    }

    /**
     * @param int $keep
     */
    protected function rollAndKeep(int $keep)
    {
        // TODO: RaK seems to force a sort(). Fix? If possible?
        $trashers = $this->roll['rolls'];
        $keepers = [];

        for ($checks = 0; $checks < $keep; $checks++) {
            $bestRoll = max($trashers);
            $keepKeys = array_keys($trashers, $bestRoll);
            $keepKey = array_shift($keepKeys);
            $keepers[] = $trashers[$keepKey];
            unset($trashers[$keepKey]);
            $trashers = array_merge($trashers);
        }
        $this->roll['rolls'] = $keepers;
        if (!empty($this->roll['throwaway'])) {
            $this->roll['throwaway'] = array_merge($trashers, $this->roll['throwaway']);
        } else {
            $this->roll['throwaway'] = $trashers;
        }
    }

    protected function doPriority($rule)
    {
        $tempRule = substr($rule,0,3);
        $keepOrDrop = 0;
        if ($tempRule == 'rak' || $tempRule == 'rad') {
            $keepOrDrop = substr($rule, -1, 1);
            $rule = $tempRule;
        }
        $numberToReRoll = 0;
        $tempRule = substr($rule,0,2);
        if ($tempRule == 'rr') {
            $numberToReRoll = substr($rule, -2, 1);
            $rule = $tempRule;
        }

        switch ($rule) {
            case 'rr':
                if ($this->sides <= $numberToReRoll) {
                    $this->container->logger->error('To use ReRoll ' . $numberToReRoll . 's the number of sides must be greater than ' . $numberToReRoll . '.');
                    throw new \Exception('To use ReRoll ' . $numberToReRoll . 's the number of sides must be greater than ' . $numberToReRoll . '.');
                }
                $this->rerollThresholdAndBelow($numberToReRoll);
                break;
            case 'rak':
                if (count($this->roll['rolls']) < $keepOrDrop) {
                    $this->container->logger->error('Roll and Keep ' . $keepOrDrop . ' requires a pool equal to or greater than ' . $keepOrDrop);
                    throw new \Exception('Roll and Drop ' . $keepOrDrop . ' requires a pool equal to or greater than ' . $keepOrDrop);
                }
                $this->rollAndKeep($keepOrDrop);
                break;
            case 'rad':
                if (count($this->roll['rolls']) <= $keepOrDrop) {
                    $this->container->logger->error('Roll and Drop ' . $keepOrDrop . ' requires a pool greater than ' . $keepOrDrop);
                    throw new \Exception('Roll and Drop ' . $keepOrDrop . ' requires a pool greater than ' . $keepOrDrop);
                }
                $this->rollAndDropLowestDice($keepOrDrop);
                break;
            case 'sort':
            case 'rsort':
                $rule($this->roll['rolls']);
                break;
            default:
        }
    }

    /**
     * Formats the Help Array as an Object for use with The Help Route.
     *
     * @return \stdClass
     */
    public function getHelp()
    {
        $this->help['priorities'] = $this->priorities;
        return (object)$this->help;
    }

    public function rollTheBones($pattern)
    {
        if (stristr($pattern, ':')) {
            $boom = explode(':', $pattern);
            $pattern = $boom[0];
            $this->rules = $boom;
        }

        if (!stristr($pattern, 'd')) {
            $this->container->logger->error("Bad Pattern: " . $pattern);
            throw new \Exception('Bad pattern given');
        }

        list($this->pool, $tempSides) = explode('d', $pattern);

        if (stristr($tempSides, ' ')) {
            list($this->sides, $this->bonus) = explode(' ', $tempSides);
        } elseif (stristr($tempSides, '+')) {
            list($this->sides, $this->bonus) = explode('+', $tempSides);
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
        $this->basicRoll();

        // TODO: simplify rad, rak, and rrXs so we don't have to define each one.
        foreach ($this->priorities as $priority => $definition) {
            if (in_array($priority, $this->rules)) {
                $this->doPriority($priority);
            }
        }

        return $this->roll['total'] = array_sum($this->roll['rolls']) + $this->bonus;
    }
}
