<?php
declare(strict_types=1);

namespace App\Template\Extension\Functions;

use CAMOO\Template\Extension\FunctionHelper;

/**
 * Class Users
 * @author CamooSarl
 */
class Users extends FunctionHelper
{
    /** @var SessionSegment $session */
    private $session;

    public function initialize() : void
    {
        $this->session = $this->request->getSession();
    }

    public function getFunctions() : array
    {
        return [
            $this->add('is_loggedin', [$this, 'isLoggedIn']),
        ];
    }

    public function isLoggedIn()
    {
        return $this->session->check('loggedin') && $this->session->read('loggedin') === true;
    }
}
