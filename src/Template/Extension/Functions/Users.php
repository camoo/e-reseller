<?php

declare(strict_types=1);

namespace App\Template\Extension\Functions;

use CAMOO\Http\SessionSegment;
use CAMOO\Template\Extension\FunctionHelper;

/**
 * Class Users
 *
 * @author CamooSarl
 */
class Users extends FunctionHelper
{
    private SessionSegment $sessionSegment;

    public function initialize(): void
    {
        $this->sessionSegment = $this->request->getSession();
    }

    public function getFunctions(): array
    {
        return [
            $this->add('is_loggedin', $this->isLoggedIn(...)),
        ];
    }

    public function isLoggedIn(): bool
    {
        return $this->sessionSegment->check('loggedin') && $this->sessionSegment->read('loggedin') === true;
    }
}
