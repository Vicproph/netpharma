<?php

namespace App\Models\Traits;

trait RelationshipTrait
{

    public function getAllRelationships()
    {
        return $this->with;
    }
}
