<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lib\Traits\UserDataTrait;
use App\Model\Rest\DomainsRest;
use App\Model\Rest\OrderRest;
use App\Model\Rest\PaymentsRest;
use CAMOO\Controller\AppController as BaseController;
use CAMOO\Controller\Component\SecurityComponent;
use CAMOO\Interfaces\RestInterface;
use CAMOO\Model\AppModel;
use CAMOO\Utils\Cart;
use CAMOO\Utils\Configure;

use function Cake\I18n\__;

/**
 * @property SecurityComponent $Security
 * @property DomainsRest       $DomainsRest
 * @property PaymentsRest      $PaymentsRest
 * @property OrderRest         $OrderRest
 */
class AppController extends BaseController
{
    use UserDataTrait;

    private array $_basket = [Cart::class, 'create'];

    public function initialize(): void
    {
        parent::initialize();
        $this->set('siteConfig', Configure::read('RESELLER_SITE'));
        $showcaseContent = Configure::read('HomeContent', []);
        $this->set('homeContent', $showcaseContent);
        $this->set('showcaseContent', $showcaseContent);
        $this->loadComponent('Security');
    }

    public function loadComponent(string $component, array $config = []): void
    {
        parent::loadComponent($component, $config);
        $name = \Cake\Utility\Inflector::classify($component);
        if (property_exists($this, $name) && $this->{$name} === null) {
            $this->{$name} = $this->getComponentCollection()?->offsetGet($name);
        }
    }

    protected function loadRest(string $restModel): void
    {
        parent::loadRest($restModel);
        if (property_exists($this, $restModel) && $this->{$restModel} === null) {
            $this->{$restModel} = $this->getRestLocator()->get(\Cake\Utility\Inflector::classify($restModel));
        }
    }

    protected function getBasketRepository(): Cart
    {
        $cart = call_user_func($this->_basket, $this->request);
        if ($this->request->getSession()->check('loggedin')) {
            $cart->setUserId($this->getUserId());
        }

        $cart->refresh();

        return $cart;
    }

    protected function getPackageById(int $id): ?array
    {
        $ahTariffs = Configure::read('RESELLER_TARIFFS');
        $tariffs = is_array($ahTariffs) && is_array($ahTariffs['tariffs'] ?? null)
            ? $ahTariffs['tariffs'] : [];

        foreach ($tariffs as $tariff) {
            if (is_array($tariff) && (int)($tariff['id'] ?? -1) === $id) {
                return $tariff;
            }
        }

        return null;
    }

    protected function isFeatureEnabled(string $feature): bool
    {
        $features = Configure::read('Features');

        if (!is_array($features) || !array_key_exists($feature, $features)) {
            $envVar = 'FEATURE_' . strtoupper($feature);
            $val = getenv($envVar);
            if ($val !== false && trim((string)$val) !== '') {
                return filter_var($val, FILTER_VALIDATE_BOOL);
            }

            return true;
        }

        return ($features[$feature] ?? false) === true;
    }

    protected function requireFeature(string $feature): void
    {
        if (!$this->isFeatureEnabled($feature)) {
            throw new App\Exception\ControllerException('This product is not available.');
        }
    }

    protected function showcaseText(string $key, string $fallback): string
    {
        $value = Configure::read('HomeContent.' . $key);

        return is_string($value) && trim($value) !== '' ? $value : $fallback;
    }

    /** @param AppModel|RestInterface $model */
    protected function showValidateErrors($model, string $flashType = 'error'): void
    {
        if (empty($model)) {
            return;
        }

        $ahErrors = $model->getErrors();
        $asFields = [];
        $asMessages = [];
        if (!empty($ahErrors)) {
            foreach ($ahErrors as $sField => $ahError) {
                $asFields[] = $sField;
                $asFieldMessages = [];
                foreach ((array)$ahError as $sMessage) {
                    if (is_scalar($sMessage) && trim((string)$sMessage) !== '') {
                        $asFieldMessages[] = trim((string)$sMessage);
                    }
                }
                if ($asFieldMessages !== []) {
                    $asMessages[] = sprintf('%s: %s', $sField, implode(', ', $asFieldMessages));
                }
            }

            if ($asFields !== []) {
                $this->set('errorFields', $asFields);
            }
        }
        if ($asMessages !== []) {
            $this->request->Flash->{$flashType}(
                __('Veuillez corriger les informations suivantes : ') . implode(' | ', $asMessages),
            );
        }
    }
}
