<?php

namespace Alexisgt01\CmsCore\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class MakeAdminCommand extends Command
{
    protected $signature = 'cms:make-admin
        {--first-name= : First name}
        {--last-name= : Last name}
        {--email= : Email address}
        {--password= : Password (min 8 characters)}';

    protected $description = 'Create an admin user with super_admin role';

    public function handle(): int
    {
        $firstName = $this->option('first-name') ?? text(
            label: 'First name',
            required: true,
        );

        $lastName = $this->option('last-name') ?? text(
            label: 'Last name',
            required: true,
        );

        $email = $this->option('email') ?? text(
            label: 'Email',
            required: true,
            validate: fn (string $value): ?string => $this->validateEmail($value),
        );

        if ($this->option('email')) {
            $error = $this->validateEmail($this->option('email'));
            if ($error) {
                $this->error($error);

                return self::FAILURE;
            }
        }

        $password = $this->option('password') ?? password(
            label: 'Password',
            required: true,
            validate: fn (string $value): ?string => strlen($value) < 8
                ? 'Password must be at least 8 characters.'
                : null,
        );

        if ($this->option('password') && strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $user->assignRole('super_admin');

        $url = url(config('cms-core.path', 'admin'));

        $this->info("Admin user {$firstName} {$lastName} ({$email}) created with super_admin role.");
        $this->info("Login at: {$url}");

        return self::SUCCESS;
    }

    protected function validateEmail(string $value): ?string
    {
        $validator = Validator::make(
            ['email' => $value],
            ['email' => 'required|email|unique:users,email'],
        );

        if ($validator->fails()) {
            return $validator->errors()->first('email');
        }

        return null;
    }
}
