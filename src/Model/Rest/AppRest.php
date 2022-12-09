<?php

declare(strict_types=1);

namespace App\Model\Rest;

use CAMOO\Interfaces\ValidationInterface;
use CAMOO\Model\Rest\AppRest as BaseRest;

/**
 * Class AppRest
 *
 * @author CamooSarl
 */
class AppRest extends BaseRest
{
    public function validationDefault(ValidationInterface $validator): ValidationInterface
    {
        return $validator;
    }
}
