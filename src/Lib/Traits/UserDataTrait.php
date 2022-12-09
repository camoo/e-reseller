<?php

declare(strict_types=1);

namespace App\Lib\Traits;

trait UserDataTrait
{
    protected function getUserId(): int
    {
        return $this->request->getSession()->check(AUTH_USER_ID) ?
            (int)$this->request->getSession()->read(AUTH_USER_ID) : 0;
    }
}
