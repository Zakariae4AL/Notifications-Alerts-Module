<?php

namespace App\Enums;

enum Severity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';

    public function label(): string
    {
        return __("notifications.severity.{$this->value}");
    }
}
