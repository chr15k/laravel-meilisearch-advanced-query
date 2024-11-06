<?php

namespace Chr15k\MeilisearchAdvancedQuery\Facades;

use Chr15k\MeilisearchAdvancedQuery\FilterBuilder as Builder;
use Illuminate\Support\Facades\Facade;

class FilterBuilder extends Facade
{
    public static function getFacadeAccessor()
    {
        return Builder::class;
    }
}
