<?php

declare(strict_types=1);

namespace App\Test;

use App\Model\Rest\DomainsRest;
use App\Model\Rest\OrderRest;
use App\Model\Rest\UsersRest;
use CAMOO\Validation\Adapters\Cake\Validator;
use PHPUnit\Framework\TestCase;

final class RestValidationTest extends TestCase
{
    public function testLoginValidationAcceptsAValidPayload(): void
    {
        $validator = new Validator();
        $this->rest(UsersRest::class)->validationLogin($validator);

        self::assertTrue($validator->isValid([
            'email' => 'customer@example.com',
            'password' => 'Secure!123',
        ]));
    }

    public function testLoginValidationRejectsInvalidCredentials(): void
    {
        $validator = new Validator();
        $this->rest(UsersRest::class)->validationLogin($validator);

        self::assertFalse($validator->isValid([
            'email' => 'not-an-email',
            'password' => 'short',
        ]));
        self::assertArrayHasKey('email', $validator->getErrors());
        self::assertArrayHasKey('password', $validator->getErrors());
    }

    public function testOrderValidationRequiresValidJsonObjectOrArray(): void
    {
        $validator = new Validator();
        $this->rest(OrderRest::class)->validationDefault($validator);

        self::assertTrue($validator->isValid(['body' => '{"items":[]}']));
        self::assertFalse($validator->isValid(['body' => '{invalid']));
    }

    public function testWhoisValidationRejectsMalformedDomains(): void
    {
        $validator = new Validator();
        $this->rest(DomainsRest::class)->validationWhois($validator);

        self::assertTrue($validator->isValid(['domain' => 'example.com']));
        self::assertFalse($validator->isValid(['domain' => '-invalid.com']));
    }

    public function testProfileEditValidationAcceptsValidPayload(): void
    {
        $validator = new Validator();
        $this->rest(UsersRest::class)->validationEdit($validator);

        self::assertTrue($validator->isValid([
            'id' => 123,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '677000000',
            'city' => 'Douala',
            'address' => 'Akwa',
        ]));
    }

    public function testProfileEditValidationRejectsMissingId(): void
    {
        $validator = new Validator();
        $this->rest(UsersRest::class)->validationEdit($validator);

        self::assertFalse($validator->isValid([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]));
        self::assertArrayHasKey('id', $validator->getErrors());
    }

    public function testContactEditValidationAcceptsValidPayload(): void
    {
        $validator = new Validator();
        $this->rest(\App\Model\Rest\ContactsRest::class)->validationEdit($validator);

        self::assertTrue($validator->isValid([
            'id' => 456,
            'name' => 'Jane Registrant',
            'email' => 'jane@example.com',
            'phone' => '+237677000001',
        ]));
    }

    public function testContactEditValidationRejectsMissingId(): void
    {
        $validator = new Validator();
        $this->rest(\App\Model\Rest\ContactsRest::class)->validationEdit($validator);

        self::assertFalse($validator->isValid([
            'name' => 'Jane Registrant',
        ]));
        self::assertArrayHasKey('id', $validator->getErrors());
    }

    public function testResendVerificationValidationAcceptsValidId(): void
    {
        $validator = new Validator();
        $this->rest(DomainsRest::class)->validationResendVerification($validator);

        self::assertTrue($validator->isValid(['id' => 789]));
    }

    public function testResendVerificationValidationRejectsMissingOrInvalidId(): void
    {
        $validator = new Validator();
        $this->rest(DomainsRest::class)->validationResendVerification($validator);

        self::assertFalse($validator->isValid([]));
        self::assertArrayHasKey('id', $validator->getErrors());

        self::assertFalse($validator->isValid(['id' => 'not-an-int']));
        self::assertArrayHasKey('id', $validator->getErrors());
    }

    /** @param class-string $class */
    private function rest(string $class): object
    {
        return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    }
}
