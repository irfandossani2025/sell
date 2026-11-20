<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('app:create-admin-user {--name=} {--email=}')]
#[Description('Create the single administrator account for Hot Products')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        if (User::query()->where('is_admin', true)->exists()) {
            $this->components->error('An administrator account already exists.');
            $this->removeTemporaryPasswordIfPresent();

            return self::FAILURE;
        }

        $name = $this->option('name') ?: ($this->input->isInteractive() ? $this->ask('Administrator name') : null);
        $email = $this->option('email') ?: ($this->input->isInteractive() ? $this->ask('Administrator email') : null);
        $password = config('services.admin_setup_password');

        if (! $password && $this->input->isInteractive()) {
            $password = $this->secret('Password (at least 12 characters)');
        }

        $confirmation = $password;

        if (! config('services.admin_setup_password') && $this->input->isInteractive()) {
            $confirmation = $this->secret('Confirm password');
        }

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $admin = new User;
        $admin->name = $name;
        $admin->email = $email;
        $admin->password = Hash::make($password);
        $admin->is_admin = true;
        $admin->save();

        $this->components->info('Administrator created. Use the email and password to sign in.');

        $this->removeTemporaryPasswordIfPresent();

        return self::SUCCESS;
    }

    private function removeTemporaryPasswordIfPresent(): void
    {
        if (! config('services.admin_setup_password')) {
            return;
        }

        if (! $this->removeTemporaryPassword()) {
            $this->components->warn('Remove HOTPRODUCTS_ADMIN_PASSWORD from the hosting environment, then clear the configuration cache.');

            return;
        }

        if (Artisan::call('config:clear') !== self::SUCCESS) {
            $this->components->warn('Temporary password removed, but configuration cache clearing failed. Clear it from Plesk.');

            return;
        }

        $this->components->info('Temporary administrator password removed from the environment file.');
    }

    private function removeTemporaryPassword(): bool
    {
        $environmentFile = base_path('.env');

        if (! is_file($environmentFile) || ! is_readable($environmentFile) || ! is_writable($environmentFile)) {
            return false;
        }

        $contents = file_get_contents($environmentFile);

        if ($contents === false) {
            return false;
        }

        $updatedContents = preg_replace('/^[ \t]*HOTPRODUCTS_ADMIN_PASSWORD[ \t]*=.*(?:\r\n|\n|\r|$)/m', '', $contents, -1, $matches);

        if ($updatedContents === null || $matches === 0) {
            return false;
        }

        if (file_put_contents($environmentFile, $updatedContents, LOCK_EX) === false) {
            return false;
        }

        putenv('HOTPRODUCTS_ADMIN_PASSWORD');
        unset($_ENV['HOTPRODUCTS_ADMIN_PASSWORD'], $_SERVER['HOTPRODUCTS_ADMIN_PASSWORD']);

        return true;
    }
}
