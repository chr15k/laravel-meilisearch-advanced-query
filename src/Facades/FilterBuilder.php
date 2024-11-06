<?php

namespace Chr15k\MeilisearchFilter\Facades;

use Chr15k\MeilisearchFilter\FilterBuilder as Builder;
use Illuminate\Support\Facades\Facade;

class FilterBuilder extends Facade
{
    public static function getFacadeAccessor()
    {
        return Builder::class;
    }
}
