<?php

declare(strict_types=1);

namespace Tapp\FilamentActivity\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tapp\FilamentActivity\Models\Traits\HasActivities;

class Post extends Model
{
    use HasActivities;

    protected $guarded = [];
}
