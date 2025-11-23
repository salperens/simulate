<?php

namespace App\MatchSimulation\Random;

use App\MatchSimulation\Contracts\RandomGenerator;

class NativeRandomGenerator implements RandomGenerator
{
    public function float01(): float
    {
        return mt_rand() / mt_getrandmax();
    }
}
