<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MarketingExpense;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExpensesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_section_listing_returns_only_marketing_records(): void
    {
        $admin = $this->makeAdmin();
        $role = $this->makeRole('Marketing QA', $admin);
        $user = $this->makeUser($role, $admin, 'MKT100');

        $marketing = $this->makeExpense([
            'section' => 'marketing',
            'marketing_person_code' => $user->user_code,
            'person_name' => $user->name,
            'amount' => 750.25,
        ]);

        $this->makeExpense([
            'section' => 'office',
            'marketing_person_code' => 'OFF999',
            'person_name' => 'Office Person',
            'amount' => 160.00,
        ]);

        $response = $this->withHeaders($this->jwtHeadersForUser($user))
            ->getJson('/api/expenses?section=marketing&per_page=10');

        $response->assertStatus(200);
        $response->assertJsonPath('filters.section', 'marketing');
        $response->assertJsonPath('totals.total_expenses', (float) $marketing->amount);

        $sections = collect($response->json('data'))->pluck('section')->unique()->all();
        $this->assertSame(['marketing'], $sections);
        $this->assertSame($marketing->id, $response->json('data.0.id'));
    }

    public function test_office_section_listing_returns_only_office_records(): void
    {
        $admin = $this->makeAdmin();
        $role = $this->makeRole('Office QA', $admin);
        $user = $this->makeUser($role, $admin, 'OFF100');

        $office = $this->makeExpense([
            'section' => 'office',
            'marketing_person_code' => $user->user_code,
            'person_name' => $user->name,
            'amount' => 325.10,
        ]);

        $this->makeExpense([
            'section' => 'marketing',
            'marketing_person_code' => 'MKT999',
            'person_name' => 'Marketing Person',
            'amount' => 99.99,
        ]);

        $response = $this->withHeaders($this->jwtHeadersForUser($user))
            ->getJson('/api/expenses?section=office&per_page=10');

        $response->assertStatus(200);
        $response->assertJsonPath('filters.section', 'office');
        $response->assertJsonPath('totals.total_expenses', (float) $office->amount);

        $sections = collect($response->json('data'))->pluck('section')->unique()->all();
        $this->assertSame(['office'], $sections);
        $this->assertSame($office->id, $response->json('data.0.id'));
    }

    public function test_personal_section_listing_returns_grouped_summary_by_default(): void
    {
        $admin = $this->makeAdmin();
        $role = $this->makeRole('Personal QA', $admin);
        $user = $this->makeUser($role, $admin, 'PER100');

        $first = $this->makeExpense([
            'section' => 'personal',
            'marketing_person_code' => $user->user_code,
            'person_name' => $user->name,
            'amount' => 120.00,
            'submitted_for_approval' => true,
            'approval_summary_path' => 'marketing_expenses/personal_nov.pdf',
        ]);

        $second = $this->makeExpense([
            'section' => 'personal',
            'marketing_person_code' => $user->user_code,
            'person_name' => $user->name,
            'amount' => 80.50,
            'submitted_for_approval' => true,
            'approval_summary_path' => 'marketing_expenses/personal_nov.pdf',
            'from_date' => now()->subDays(2),
            'to_date' => now()->subDay(),
        ]);

        $response = $this->withHeaders($this->jwtHeadersForUser($user))
            ->getJson('/api/expenses?section=personal&per_page=10');

        $response->assertStatus(200);
        $response->assertJsonPath('filters.group_personal', true);

        $this->assertCount(1, $response->json('data'));
        $aggregateIds = $response->json('data.0.aggregate_ids');
        $this->assertIsArray($aggregateIds);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $aggregateIds);
        $this->assertSame(200.5, $response->json('data.0.amount'));
    }

    public function test_personal_section_listing_can_return_individual_items_when_grouping_disabled(): void
    {
        $admin = $this->makeAdmin();
        $role = $this->makeRole('Personal Raw QA', $admin);
        $user = $this->makeUser($role, $admin, 'PER200');

        $expenses = [
            $this->makeExpense([
                'section' => 'personal',
                'marketing_person_code' => $user->user_code,
                'person_name' => $user->name,
                'amount' => 60.00,
                'submitted_for_approval' => true,
                'from_date' => now()->subDays(5),
                'to_date' => now()->subDays(4),
            ]),
            $this->makeExpense([
                'section' => 'personal',
                'marketing_person_code' => $user->user_code,
                'person_name' => $user->name,
                'amount' => 40.00,
                'submitted_for_approval' => true,
                'from_date' => now()->subDays(3),
                'to_date' => now()->subDays(2),
            ]),
        ];

        $response = $this->withHeaders($this->jwtHeadersForUser($user))
            ->getJson('/api/expenses?section=personal&group_personal=0&per_page=10');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));

        $returnedIds = collect($response->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing(collect($expenses)->pluck('id')->all(), $returnedIds);
        $this->assertNull($response->json('data.0.aggregate_ids'));
    }

    public function test_admin_can_list_each_section_of_expenses(): void
    {
        $admin = $this->makeAdmin();
        $role = $this->makeRole('Full Access QA', $admin);
        $user = $this->makeUser($role, $admin, 'ADM100');

        $marketing = $this->makeExpense([
            'section' => 'marketing',
            'marketing_person_code' => $user->user_code,
            'person_name' => $user->name,
            'amount' => 500.00,
        ]);

        $office = $this->makeExpense([
            'section' => 'office',
            'marketing_person_code' => 'OFF200',
            'person_name' => 'Office Tester',
            'amount' => 250.00,
        ]);

        $personal = [
            $this->makeExpense([
                'section' => 'personal',
                'marketing_person_code' => 'PER300',
                'person_name' => 'Personal Tester',
                'amount' => 90.00,
                'submitted_for_approval' => true,
            ]),
            $this->makeExpense([
                'section' => 'personal',
                'marketing_person_code' => 'PER300',
                'person_name' => 'Personal Tester',
                'amount' => 30.00,
                'submitted_for_approval' => true,
                'from_date' => now()->subDays(1),
                'to_date' => now(),
            ]),
        ];

        $headers = $this->jwtHeadersForAdmin($admin);

        $marketingResponse = $this->withHeaders($headers)
            ->getJson('/api/admin/expenses?section=marketing&per_page=10');
        $marketingResponse->assertStatus(200);
        $this->assertSame($marketing->id, $marketingResponse->json('data.0.id'));

        $officeResponse = $this->withHeaders($headers)
            ->getJson('/api/admin/expenses?section=office&per_page=10');
        $officeResponse->assertStatus(200);
        $this->assertSame($office->id, $officeResponse->json('data.0.id'));

        $personalResponse = $this->withHeaders($headers)
            ->getJson('/api/admin/expenses?section=personal&per_page=10');
        $personalResponse->assertStatus(200);
        $this->assertCount(1, $personalResponse->json('data'));
        $aggregate = $personalResponse->json('data.0.aggregate_ids');
        $this->assertEqualsCanonicalizing(collect($personal)->pluck('id')->all(), $aggregate);
    }

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin '.Str::random(4),
            'email' => Str::uuid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);
    }

    private function makeRole(string $name, ?Admin $creator = null): Role
    {
        return Role::create([
            'role_name' => $name.' '.Str::random(4),
            'created_by' => $creator?->id,
            'updated_by' => $creator?->id,
        ]);
    }

    private function makeUser(Role $role, ?Admin $creator = null, ?string $code = null): User
    {
        return User::create([
            'name' => 'Test User '.Str::random(4),
            'user_code' => $code ?? strtoupper(Str::random(6)),
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'created_by' => $creator?->id,
            'updated_by' => $creator?->id,
        ]);
    }

    private function makeExpense(array $overrides = []): MarketingExpense
    {
        $defaults = [
            'marketing_person_code' => 'CODE'.strtoupper(Str::random(4)),
            'person_name' => 'Person '.Str::random(4),
            'section' => 'marketing',
            'amount' => 100.00,
            'approved_amount' => 0.0,
            'from_date' => now()->startOfMonth(),
            'to_date' => now()->endOfMonth(),
            'file_path' => null,
            'description' => null,
            'approval_note' => null,
            'approval_summary_path' => null,
            'status' => 'pending',
            'submitted_for_approval' => false,
            'approved_by' => null,
            'approved_at' => null,
        ];

        return MarketingExpense::create(array_merge($defaults, $overrides));
    }

    private function jwtHeadersForUser(User $user): array
    {
        $token = auth('api')->login($user);

        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    private function jwtHeadersForAdmin(Admin $admin): array
    {
        $token = auth('api_admin')->login($admin);

        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }
}
