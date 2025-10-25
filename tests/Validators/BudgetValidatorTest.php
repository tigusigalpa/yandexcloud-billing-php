<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Tests\Validators;

use PHPUnit\Framework\TestCase;
use Tigusigalpa\YandexCloudBilling\Exceptions\ValidationException;
use Tigusigalpa\YandexCloudBilling\Validators\BudgetValidator;

class BudgetValidatorTest extends TestCase
{
    public function testValidateCreateDataSuccess(): void
    {
        $validData = [
            'billingAccountId' => 'account123',
            'name' => 'Test Budget',
            'amount' => '1000.50',
            'resetPeriod' => 'MONTHLY'
        ];

        // Should not throw exception
        BudgetValidator::validateCreateData($validData);
        $this->assertTrue(true);
    }

    public function testValidateCreateDataMissingBillingAccountId(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('billingAccountId is required');

        BudgetValidator::validateCreateData(['name' => 'Test Budget']);
    }

    public function testValidateCreateDataMissingName(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('name is required');

        BudgetValidator::validateCreateData(['billingAccountId' => 'account123']);
    }

    public function testValidateCreateDataInvalidAmount(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('amount must be numeric');

        BudgetValidator::validateCreateData([
            'billingAccountId' => 'account123',
            'name' => 'Test Budget',
            'amount' => 'invalid'
        ]);
    }

    public function testValidateCreateDataInvalidResetPeriod(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('resetPeriod must be one of: MONTHLY, QUARTERLY, ANNUALLY');

        BudgetValidator::validateCreateData([
            'billingAccountId' => 'account123',
            'name' => 'Test Budget',
            'resetPeriod' => 'INVALID'
        ]);
    }

    public function testValidateThresholdRulesSuccess(): void
    {
        $validRules = [
            ['thresholdValue' => 50, 'thresholdType' => 'PERCENT'],
            ['thresholdValue' => 1000, 'thresholdType' => 'AMOUNT']
        ];

        // Should not throw exception
        BudgetValidator::validateThresholdRules($validRules);
        $this->assertTrue(true);
    }

    public function testValidateThresholdRulesInvalidValue(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Each threshold rule must have a numeric thresholdValue');

        BudgetValidator::validateThresholdRules([
            ['thresholdValue' => 'invalid']
        ]);
    }

    public function testValidateThresholdRulesInvalidType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('thresholdType must be either PERCENT or AMOUNT');

        BudgetValidator::validateThresholdRules([
            ['thresholdValue' => 50, 'thresholdType' => 'INVALID']
        ]);
    }
}