<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'workos_id' => 'user_01JX6KAZ7FBR7Y9DA9FNGRAJWV',
        ]);

        // Create 3 forms with realistic data using the factory
        $forms = [
            Form::factory()->create([
                'name' => 'Contact Us',
                'user_id' => $user->id,
            ]),
            Form::factory()->create([
                'name' => 'Feedback',
                'user_id' => $user->id,
            ]),
            Form::factory()->create([
                'name' => 'Support Request',
                'user_id' => $user->id,
            ]),
        ];

        // Helper to create many submissions for a form
        $createSubmissions = function ($form, $submissions) {
            foreach ($submissions as $submission) {
                FormSubmission::factory()->create([
                    'form_id' => $form->id,
                    'data' => [
                        'name' => $submission['name'],
                        'email' => $submission['email'],
                        'message' => $submission['message'],
                    ],
                    'ip_address' => $submission['ip_address'],
                    'user_agent' => $submission['user_agent'],
                    'referrer' => $submission['referrer'],
                ]);
            }
        };

        // Contact Us submissions
        $createSubmissions($forms[0], [
            [ 'name' => 'Alice Johnson', 'email' => 'alice.johnson@example.com', 'message' => 'I am interested in your services. Please contact me back.', 'ip_address' => '192.168.1.10', 'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Bob Smith', 'email' => 'bob.smith@example.com', 'message' => 'Can you provide more information about your pricing?', 'ip_address' => '192.168.1.11', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Emily Turner', 'email' => 'emily.turner@example.com', 'message' => 'Do you offer discounts for non-profits?', 'ip_address' => '192.168.1.14', 'user_agent' => 'Mozilla/5.0 (iPad; CPU OS 13_2 like Mac OS X)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Javier Morales', 'email' => 'javier.morales@example.com', 'message' => 'What are your business hours?', 'ip_address' => '192.168.1.18', 'user_agent' => 'Mozilla/5.0 (Linux; Android 10)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Priya Singh', 'email' => 'priya.singh@example.com', 'message' => 'Can I schedule a call with your team?', 'ip_address' => '192.168.1.19', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Lucas Brown', 'email' => 'lucas.brown@example.com', 'message' => 'Do you have a newsletter?', 'ip_address' => '192.168.1.20', 'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_0_1)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Sofia Rossi', 'email' => 'sofia.rossi@example.com', 'message' => 'I would like to request a demo.', 'ip_address' => '192.168.1.21', 'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Ahmed Hassan', 'email' => 'ahmed.hassan@example.com', 'message' => 'Is your service available internationally?', 'ip_address' => '192.168.1.22', 'user_agent' => 'Mozilla/5.0 (Linux; Android 12)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Mia Chen', 'email' => 'mia.chen@example.com', 'message' => 'How do I reset my password?', 'ip_address' => '192.168.1.23', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Noah Kim', 'email' => 'noah.kim@example.com', 'message' => 'Can I get a copy of my previous submissions?', 'ip_address' => '192.168.1.24', 'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'referrer' => 'https://www.example.com/contact' ],
            [ 'name' => 'Zara Patel', 'email' => 'zara.patel@example.com', 'message' => 'Do you offer support in other languages?', 'ip_address' => '192.168.1.25', 'user_agent' => 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X)', 'referrer' => 'https://www.example.com/contact' ],
        ]);

        // Feedback submissions
        $createSubmissions($forms[1], [
            [ 'name' => 'Charlie Lee', 'email' => 'charlie.lee@example.com', 'message' => 'Great website! Very easy to use.', 'ip_address' => '192.168.1.12', 'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Fatima Noor', 'email' => 'fatima.noor@example.com', 'message' => 'The feedback form was simple and quick to fill out.', 'ip_address' => '192.168.1.15', 'user_agent' => 'Mozilla/5.0 (Android 12; Mobile)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Liam Evans', 'email' => 'liam.evans@example.com', 'message' => 'I appreciate the fast response from your team.', 'ip_address' => '192.168.1.26', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Samantha Green', 'email' => 'samantha.green@example.com', 'message' => 'The UI is very intuitive.', 'ip_address' => '192.168.1.27', 'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 12_0_1)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Omar Farouk', 'email' => 'omar.farouk@example.com', 'message' => 'I found a bug on the feedback page.', 'ip_address' => '192.168.1.28', 'user_agent' => 'Mozilla/5.0 (Linux; Android 13)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Julia Müller', 'email' => 'julia.muller@example.com', 'message' => 'Thank you for adding dark mode!', 'ip_address' => '192.168.1.29', 'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Pedro Alvarez', 'email' => 'pedro.alvarez@example.com', 'message' => 'The mobile experience is excellent.', 'ip_address' => '192.168.1.30', 'user_agent' => 'Mozilla/5.0 (Android 11; Mobile)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Hannah Kim', 'email' => 'hannah.kim@example.com', 'message' => 'I would love to see more integrations.', 'ip_address' => '192.168.1.31', 'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Ravi Patel', 'email' => 'ravi.patel@example.com', 'message' => 'Can you add a print option for submissions?', 'ip_address' => '192.168.1.32', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Isabella Costa', 'email' => 'isabella.costa@example.com', 'message' => 'The feedback form is very helpful.', 'ip_address' => '192.168.1.33', 'user_agent' => 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X)', 'referrer' => 'https://www.example.com/feedback' ],
            [ 'name' => 'Tomáš Novák', 'email' => 'tomas.novak@example.com', 'message' => 'I like the new design!', 'ip_address' => '192.168.1.34', 'user_agent' => 'Mozilla/5.0 (Linux; Android 10)', 'referrer' => 'https://www.example.com/feedback' ],
        ]);

        // Support Request submissions
        $createSubmissions($forms[2], [
            [ 'name' => 'Dana White', 'email' => 'dana.white@example.com', 'message' => 'I need help with my account login.', 'ip_address' => '192.168.1.13', 'user_agent' => 'Mozilla/5.0 (Linux; Android 11)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'Gregory Miles', 'email' => 'gregory.miles@example.com', 'message' => 'My support ticket has not been answered yet.', 'ip_address' => '192.168.1.16', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'Helen Zhou', 'email' => 'helen.zhou@example.com', 'message' => 'Is there a phone number for urgent support?', 'ip_address' => '192.168.1.17', 'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'Samuel Okoro', 'email' => 'samuel.okoro@example.com', 'message' => 'How do I update my billing information?', 'ip_address' => '192.168.1.35', 'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'Linda Park', 'email' => 'linda.park@example.com', 'message' => 'I am experiencing issues with notifications.', 'ip_address' => '192.168.1.36', 'user_agent' => 'Mozilla/5.0 (Android 12; Mobile)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'Alexei Ivanov', 'email' => 'alexei.ivanov@example.com', 'message' => 'Can you help me recover deleted data?', 'ip_address' => '192.168.1.37', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'Maria Garcia', 'email' => 'maria.garcia@example.com', 'message' => 'How do I change my email address?', 'ip_address' => '192.168.1.38', 'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 12_0_1)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'John Doe', 'email' => 'john.doe@example.com', 'message' => 'I am locked out of my account.', 'ip_address' => '192.168.1.39', 'user_agent' => 'Mozilla/5.0 (Linux; Android 13)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'Sara Svensson', 'email' => 'sara.svensson@example.com', 'message' => 'The support chat is not loading.', 'ip_address' => '192.168.1.40', 'user_agent' => 'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'Mohammed Al-Farsi', 'email' => 'mohammed.alfarsi@example.com', 'message' => 'How do I delete my account?', 'ip_address' => '192.168.1.41', 'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'referrer' => 'https://www.example.com/support' ],
            [ 'name' => 'Chloe Dubois', 'email' => 'chloe.dubois@example.com', 'message' => 'I need to update my payment method.', 'ip_address' => '192.168.1.42', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'referrer' => 'https://www.example.com/support' ],
        ]);
    }
}
