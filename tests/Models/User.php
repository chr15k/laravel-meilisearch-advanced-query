<?php

namespace Chr15k\MeilisearchAdvancedQuery\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class User extends Model
{
    use Searchable;
}