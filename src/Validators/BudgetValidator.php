<?php

declare(strict_types=1);

namespace Tigusigalpa\YandexCloudBilling\Validators;

use Tigusigalpa\YandexCloudBilling\Exceptions\ValidationException;

class BudgetValidator
{
    /**
     * Валидация данных бюджета для создания
     *
     * @throws ValidationException
     */
    public static function validateCreateData(array $data): void
    {
        if (empty($data['billingAccountId'])) {
            throw new ValidationException('billingAccountId is required');
        }

        if (empty($data['name'])) {
            throw new ValidationException('name is required');
        }

        if (isset($data['amount']) && !is_numeric($data['amount'])) {
            throw new ValidationException('amount must be numeric');
        }

        if (isset($data['resetPeriod']) && !in_array($data['resetPeriod'], ['MONTHLY', 'QUARTERLY', 'ANNUALLY'])) {
            throw new ValidationException('resetPeriod must be one of: MONTHLY, QUARTERLY, ANNUALLY');
        }

        if (isset($data['thresholdRules']) && !is_array($data['thresholdRules'])) {
            throw new ValidationException('thresholdRules must be an array');
        }
    }

    /**
     * Валидация данных бюджета для обновления
     *
     * @throws ValidationException
     */
    public static function validateUpdateData(array $data): void
    {
        if (isset($data['amount']) && !is_numeric($data['amount'])) {
            throw new ValidationException('amount must be numeric');
        }

        if (isset($data['resetPeriod']) && !in_array($data['resetPeriod'], ['MONTHLY', 'QUARTERLY', 'ANNUALLY'])) {
            throw new ValidationException('resetPeriod must be one of: MONTHLY, QUARTERLY, ANNUALLY');
        }

        if (isset($data['thresholdRules']) && !is_array($data['thresholdRules'])) {
            throw new ValidationException('thresholdRules must be an array');
        }
    }

    /**
     * Валидация правил порогов
     *
     * @throws ValidationException
     */
    public static function validateThresholdRules(array $rules): void
    {
        foreach ($rules as $rule) {
            if (!isset($rule['thresholdValue']) || !is_numeric($rule['thresholdValue'])) {
                throw new ValidationException('Each threshold rule must have a numeric thresholdValue');
            }

            if (isset($rule['thresholdType']) && !in_array($rule['thresholdType'], ['PERCENT', 'AMOUNT'])) {
                throw new ValidationException('thresholdType must be either PERCENT or AMOUNT');
            }
        }
    }
}