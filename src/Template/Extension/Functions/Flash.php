<?php
declare(strict_types=1);

namespace App\Template\Extension\Functions;

use CAMOO\Template\Extension\FunctionHelper;
use CAMOO\Utils\Configure;

/**
 * Class Flash
 * @author CamooSarl
 */
class Flash extends FunctionHelper
{

    /** @var \CAMOO\Http\Flash $flash */
    private $flash;

    public function initialize() : void
    {
        $this->flash = $this->request->Flash;
    }

    public function getFunctions() : array
    {
        return [
            $this->add('show_flash', [$this, 'display']),
        ];
    }

    public function display(string $key='flash')
    {
        if ($flash = $this->request->getSession()->read('CAMOO.SYS.FLASH')) {
            foreach ($flash as $keyContainer => $alert) {
				if (null === $alert) {
					continue;
				}
                if ($key === $keyContainer) {
                    return $this->{$alert}($this->flash->get($key));
                }
            }
        }
    }

    private function success(string $message) : string
    {
        return $this->buildAlert('success', $message);
    }

    private function info(string $message) : string
    {
        return $this->buildAlert('info', $message);
    }

    private function warning(string $message) : string
    {
        return $this->buildAlert('warning', $message);
    }

    private function error(string $message) : string
    {
        return $this->buildAlert('danger', $message);
    }

    private function default(string $message) : string
    {
        return $this->buildAlert('secondary', $message);
    }

    private function buildAlert(string $class, string $message) : string
    {
        return '<div class="alert alert-'.$class.' alert-dismissible">
  <button type="button" class="close" data-dismiss="alert">&times;</button>'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</div>';
    }
}
