<?php

namespace App\Enums;

enum Role: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case TECH_MANAGER = 'tech_manager';
    case QUALITY_MANAGER = 'quality_manager';
    case LAB_ANALYST = 'lab_analyst';
    case COMPUTER_OPERATOR = 'computer_operator';
    case COMPUTER_INCHARGE = 'computer_incharge';
    case GENERAL_MANAGER = 'general_manager';
    case RECEPTIONIST = 'receptionist';
    case OFFICE_COORDINATOR = 'office_coordinator';
    case MARKETING_PERSON = 'marketing_person'; 

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    } 

    public static function labels(): array
    {
        return [
            self::ADMIN->value => 'Admin',
            self::TECH_MANAGER->value => 'Tech Manager',
            self::QUALITY_MANAGER->value => 'Quality Manager',
            self::LAB_ANALYST->value => 'Lab Analyst',
            self::COMPUTER_OPERATOR->value => 'Computer Operator',
            self::COMPUTER_INCHARGE->value => 'Computer Incharge',
            self::GENERAL_MANAGER->value => 'General Manager',
            self::RECEPTIONIST->value => 'Receptionist',
            self::OFFICE_COORDINATOR->value => 'Office Coordinator',
            self::MARKETING_PERSON->value => 'Marketing Person',
        ];
    }
}