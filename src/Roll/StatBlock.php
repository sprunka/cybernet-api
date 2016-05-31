<?php
/**
 * Created by PhpStorm.
 * User: Sean
 * Date: 2016.05.29
 * Time: 18:55
 */

namespace CybernetAPI\Roll;


use CybernetAPI\AbstractRoute;
use Slim\Http\Request;
use Slim\Http\Response;

class StatBlock extends AbstractRoute
{
    protected $allowed = [
        'standard' => '4d6:rak3:flex',
        'hardcore' => '3d6:fixed',
        'heroic' => '1d10+8:flex',
        'heroic_ordered' => '1d10+8:fixed',
        'unique' => '6d6:rak3:rr2s:fixed'
    ];
    protected $variant = 'standard';
    protected $attributes = ['STR', 'DEX', 'CON', 'INT', 'WIS', 'CHA'];
    protected $dice;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->dice = new Dice($container);
    }

    public function __invoke(Request $request, Response $response)
    {
        $this->variant = $request->getAttribute('variant', $this->variant);

        $statBlock = [];
        /** @var \stdClass $settings */
        $settings = (object)$this->container->settings->get('statBlock');
        if ($settings->pointBuy['allowed'] && strtolower($this->variant) == 'pointbuy') {
            $statBlock['points'] = $settings->pointBuy['points'];
        } else {
            for ($rollNum = 0; $rollNum < 6; $rollNum++) {
                if (stristr($this->allowed[$this->variant], 'fixed')) {
                    $key = $this->attributes[$rollNum];
                } else {
                    $key = $rollNum;
                }
                $statBlock[$key] = $this->dice->rollTheBones($this->allowed[$this->variant]);
            }
            if (!stristr($this->allowed[$this->variant], 'fixed')) {
                rsort($statBlock);
            }
        }

        $jsonResponse = $response->withJson($statBlock);
        return $jsonResponse;
    }
}
