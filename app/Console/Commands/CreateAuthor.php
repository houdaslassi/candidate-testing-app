<?php

namespace App\Console\Commands;

use App\Services\CandidateApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class CreateAuthor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'author:create 
                            {--email= : API login email}
                            {--password= : API login password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new author via the Candidate Testing API';

    /**
     * Execute the console command.
     */
    public function handle(CandidateApiClient $api): int
    {
        $this->info('=== Create New Author ===');
        $this->newLine();

        // Get API credentials
        $email = $this->option('email') ?? $this->ask('Enter API email', 'ahsoka.tano@royal-apps.io');
        $password = $this->option('password') ?? $this->secret('Enter API password');

        if (!$password) {
            $this->error('Password is required.');
            return Command::FAILURE;
        }

        // Authenticate
        $this->info('Authenticating...');
        try {
            $loginResponse = $api->login($email, $password);
            Session::put('api_token', $loginResponse['token_key']);
            $this->info('✓ Authentication successful!');
        } catch (\Exception $e) {
            $this->error('Authentication failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();

        // Collect author information
        $firstName = $this->ask('First name');
        $lastName = $this->ask('Last name');
        $birthday = $this->ask('Birthday (YYYY-MM-DD)', date('Y-m-d'));
        $gender = $this->choice('Gender', ['male', 'female'], 0);
        $placeOfBirth = $this->ask('Place of birth');
        $biography = $this->ask('Biography (optional)', '');

        // Confirm
        $this->newLine();
        $this->info('Author Details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['First Name', $firstName],
                ['Last Name', $lastName],
                ['Birthday', $birthday],
                ['Gender', $gender],
                ['Place of Birth', $placeOfBirth],
                ['Biography', $biography ?: '(empty)'],
            ]
        );

        if (!$this->confirm('Do you want to create this author?', true)) {
            $this->warn('Operation cancelled.');
            return Command::SUCCESS;
        }

        // Create author
        $this->info('Creating author...');
        try {
            $authorData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birthday' => $birthday,
                'gender' => $gender,
                'place_of_birth' => $placeOfBirth,
                'biography' => $biography,
            ];

            $result = $api->createAuthor($authorData);

            $this->newLine();
            $this->info('✓ Author created successfully!');
            $this->info('Author ID: ' . ($result['id'] ?? 'N/A'));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to create author: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
