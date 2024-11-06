<?php

namespace Chr15k\MeilisearchFilter\Contracts;

interface FilterSegment
{
    public function compile(): string;
}
