<?php
/**
 * Created by PhpStorm.
 * User: Sean
 * Date: 2016.05.07
 * Time: 17:31
 */

namespace Roll;


class Dice
{
    static function roll($pattern)
    {
        $sides = 6;
        $quantity = 3;
        $return = [];
        $return['total'] = 0;

        for ($rollNum = 1; $rollNum <= $quantity; $rollNum++) {
            $thisRoll = rand(1,$sides);
            $return[] = $thisRoll;
            $return['total'] += $thisRoll;
        }

        $json = json_encode($return);
        return $json;

    }

}
