<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Utils\Constants;

class ActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
         $builder->where('active', Constants::$CATEGORY_STATUS_ACTIVE);
    }
}
