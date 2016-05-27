<?php

namespace CybernetAPI\Choose;

use CybernetAPI\AbstractRoute;
use Faker\Generator;
use Slim\Http\Request as Request;
use Slim\Http\Response as Response;


class PersonName extends AbstractRoute
{
    /** @var  Generator */
    protected $faker;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->faker = $this->container->faker;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return string JSON Encoded
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response)
    {
        $gender = strtolower($request->getAttribute('gender',null));
        $firstLastFull = strtolower($request->getAttribute('firstLastFull','full'));
        switch ($firstLastFull) {
            case 'first':
                $name = $this->faker->firstName($gender);
                break;
            case 'last':
                $name = $this->faker->lastName;
                break;
            default:
                $name = $this->faker->name($gender);
        }

        $jsonResponse = $response->withJson($name, 200);
        return $jsonResponse;

    }
}
