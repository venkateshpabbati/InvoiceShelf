<?php

// Pilot behavioural suite — settings store semantics, cron webhook, admin dashboard.
// Spec: platform-operations-spec.md §1, §5, §6.

use App\Domains\Accounts\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

    $user = User::where('role', 'super admin')->first();
    $this->withHeaders(['company' => $user->companies()->first()->id]);
    Sanctum::actingAs($user, ['*']);
});

it('upserts settings by key and reads them back', function () {
    postJson('/api/v1/settings', ['settings' => ['pilot_key_a' => 'v1', 'pilot_key_b' => 'v2']])
        ->assertOk()
        ->assertJson(['success' => true]);

    postJson('/api/v1/settings', ['settings' => ['pilot_key_a' => 'v3']])->assertOk();

    expect(DB::table('settings')->where('option', 'pilot_key_a')->count())->toBe(1);
    expect(DB::table('settings')->where('option', 'pilot_key_a')->value('value'))->toBe('v3');

    getJson('/api/v1/settings?key=pilot_key_a')->assertOk()->assertJson(['pilot_key_a' => 'v3']);
});

it('reads a missing setting as null', function () {
    getJson('/api/v1/settings?key=pilot_absent_key')
        ->assertOk()
        ->assertJson(['pilot_absent_key' => null]);
});

it('validates the settings endpoints', function () {
    postJson('/api/v1/settings', [])->assertStatus(422)->assertJsonValidationErrors(['settings']);
    getJson('/api/v1/settings')->assertStatus(422);
});

it('guards the cron webhook with the shared token', function () {
    config(['services.cron_job.auth_token' => 'pilot-cron-token']);

    getJson('/api/cron')->assertUnauthorized();

    $this->withHeaders(['x-authorization-token' => 'wrong'])
        ->getJson('/api/cron')->assertUnauthorized();

    $this->withHeaders(['x-authorization-token' => 'pilot-cron-token'])
        ->getJson('/api/cron')->assertOk()->assertJson(['success' => true]);
});

it('reports versions and row counts on the admin dashboard', function () {
    $payload = getJson('/api/v1/super-admin/dashboard')->assertOk()->json();

    $expected = preg_replace('~[\r\n]+~', '', file_get_contents(base_path('version.md')));
    expect($payload['app_version'])->toBe($expected);
    expect($payload['php_version'])->toBe(phpversion());
    expect($payload['database']['driver'])->toBe(config('database.default'));
    expect($payload['counts']['companies'])->toBe(DB::table('companies')->count());
    expect($payload['counts']['users'])->toBe(DB::table('users')->count());
});
