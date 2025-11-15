<?php

namespace Database\Seeders;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class EmployeeSeeder extends Seeder
{
    /**
     * Seed the employees table with some mock records.
     */
    public function run(): void
    {
        $records = [
            [
                'employee_code' => 'EMP-001',
                'first_name' => 'Aditi',
                'last_name' => 'Sharma',
                'email' => 'aditi.sharma@example.com',
                'phone_primary' => '+91 98765 43210',
                'designation' => 'Head of Operations',
                'department' => 'Operations',
                'date_of_joining' => Carbon::parse('2021-04-12'),
                'employment_status' => 'active',
                'bio' => 'Oversees daily lab operations and ensures ISO compliance across locations.',
                'city' => 'Indore',
                'state' => 'Madhya Pradesh',
                'country' => 'India',
                'ctc' => 1800000,
                'dob' => Carbon::parse('1986-09-18'),
                'gender' => 'female',
                'blood_group' => 'B+',
                'bank_name' => 'HDFC Bank',
                'bank_account_name' => 'Aditi Sharma',
                'bank_account_number' => '50100234567890',
                'bank_ifsc' => 'HDFC0001234',
                'additional_details' => ['skills' => ['Leadership', 'Process Optimisation', 'Client Management']],
                'manager_email' => null,
            ],
            [
                'employee_code' => 'EMP-002',
                'first_name' => 'Rohan',
                'last_name' => 'Verma',
                'email' => 'rohan.verma@example.com',
                'phone_primary' => '+91 91234 56780',
                'designation' => 'Senior Lab Analyst',
                'department' => 'Quality Assurance',
                'date_of_joining' => Carbon::parse('2022-01-05'),
                'employment_status' => 'active',
                'bio' => 'Handles complex material testing projects and mentors junior analysts.',
                'city' => 'Bhopal',
                'state' => 'Madhya Pradesh',
                'country' => 'India',
                'ctc' => 960000,
                'dob' => Carbon::parse('1990-01-22'),
                'gender' => 'male',
                'blood_group' => 'O+',
                'additional_details' => ['certifications' => ['NABL Lead Assessor']],
                'manager_email' => 'aditi.sharma@example.com',
            ],
            [
                'employee_code' => 'EMP-003',
                'first_name' => 'Megha',
                'last_name' => 'Kulkarni',
                'email' => 'megha.kulkarni@example.com',
                'phone_primary' => '+91 90123 45678',
                'designation' => 'HR Business Partner',
                'department' => 'Human Resources',
                'date_of_joining' => Carbon::parse('2023-03-15'),
                'employment_status' => 'active',
                'bio' => 'Leads talent management, onboarding and policy rollouts for all branches.',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'country' => 'India',
                'ctc' => 840000,
                'dob' => Carbon::parse('1992-07-02'),
                'gender' => 'female',
                'blood_group' => 'A-',
                'bank_name' => 'ICICI Bank',
                'bank_account_name' => 'Megha Kulkarni',
                'bank_account_number' => '123456789012',
                'bank_ifsc' => 'ICIC0000456',
                'manager_email' => 'aditi.sharma@example.com',
            ],
            [
                'employee_code' => 'EMP-004',
                'first_name' => 'Sahil',
                'last_name' => 'Patel',
                'email' => 'sahil.patel@example.com',
                'phone_primary' => '+91 99887 66554',
                'designation' => 'Business Development Manager',
                'department' => 'Sales',
                'date_of_joining' => Carbon::parse('2020-11-01'),
                'employment_status' => 'probation',
                'bio' => 'Expands corporate client base and drives business for new testing services.',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'country' => 'India',
                'ctc' => 720000,
                'dob' => Carbon::parse('1993-11-10'),
                'gender' => 'male',
                'blood_group' => 'AB+',
                'additional_details' => ['territory' => 'Western India'],
                'manager_email' => 'megha.kulkarni@example.com',
            ],
            [
                'employee_code' => 'EMP-005',
                'first_name' => 'Farah',
                'last_name' => 'Iqbal',
                'email' => 'farah.iqbal@example.com',
                'phone_primary' => '+91 90909 80808',
                'designation' => 'Junior Lab Technician',
                'department' => 'Quality Assurance',
                'date_of_joining' => Carbon::parse('2024-06-18'),
                'employment_status' => 'active',
                'bio' => 'Supports QA team with sample preparation, documentation and reporting.',
                'city' => 'Indore',
                'state' => 'Madhya Pradesh',
                'country' => 'India',
                'ctc' => 420000,
                'dob' => Carbon::parse('1998-04-30'),
                'gender' => 'female',
                'blood_group' => 'O-',
                'manager_email' => 'rohan.verma@example.com',
            ],
        ];

        $created = [];
        $managerLookup = [];

        foreach ($records as $record) {
            $managerLookup[$record['email']] = $record['manager_email'];
            $payload = Arr::except($record, ['manager_email']);

            $employee = Employee::query()->updateOrCreate(
                ['email' => $payload['email']],
                $payload
            );

            $created[$employee->email] = $employee;
        }

        foreach ($created as $email => $employee) {
            $managerEmail = $managerLookup[$email];

            if ($managerEmail && isset($created[$managerEmail])) {
                $employee->manager()->associate($created[$managerEmail]);
                $employee->save();
            }
        }
    }
}
