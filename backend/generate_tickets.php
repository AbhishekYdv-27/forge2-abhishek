<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$users = \App\Models\User::all();
if ($users->isEmpty()) {
    echo "No users found. Create users first.\n";
    exit(1);
}

// Create or get an organization
$org = \App\Models\Organization::firstOrCreate(
    ['name' => 'Acme Corp'],
    ['slug' => 'acme-corp']
);

$statuses = ['open', 'pending', 'resolved', 'closed'];
$titles = [
    'Login page not loading',
    'Email notifications not sending',
    'Dashboard performance issues',
    'API endpoint returning 500 error',
    'Database connection timeout',
    'Mobile app crashes on startup',
    'Payment processing failed',
    'User profile cannot be updated',
    'Export to CSV not working',
    'Two-factor authentication disabled',
];

$descriptions = [
    'The login page is showing a blank screen when I try to access it.',
    'I\'m not receiving email confirmations for my account registration.',
    'The dashboard takes too long to load, sometimes 30+ seconds.',
    'The API keeps returning 500 errors intermittently.',
    'Getting frequent database timeout errors in production.',
    'The mobile app keeps crashing immediately after launch.',
    'Payment transactions are failing with no error message.',
    'Cannot edit my profile information through the UI.',
    'The export feature is broken for CSV downloads.',
    'Two-factor authentication seems to be disabled in production.',
];

for ($i = 0; $i < 10; $i++) {
    $requester = $users->random();
    $assignee = $users->random();
    $ticket = \App\Models\Ticket::create([
        'subject' => $titles[$i],
        'description' => $descriptions[$i],
        'status' => $statuses[array_rand($statuses)],
        'priority' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
        'requester_id' => $requester->id,
        'assignee_id' => $assignee->id,
        'organization_id' => $org->id,
    ]);

    // Add 1-3 comments to each ticket
    $commentCount = rand(1, 3);
    for ($j = 0; $j < $commentCount; $j++) {
        \App\Models\Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $users->random()->id,
            'body' => 'Comment ' . ($j + 1) . ' on this ticket. This is a sample response to the issue.',
        ]);
    }
}

echo "Generated 10 tickets with comments!\n";
