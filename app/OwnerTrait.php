<?php

namespace App;

trait OwnerTrait
{
    public function insertData($owner, $data)
    {
        $owner->insert($data);
    }

    public function updateData($owner, $data)
    {
        $owner->update($data);
    }
}
