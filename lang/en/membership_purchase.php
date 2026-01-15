<?php

return [
    'auth' => [
        'title' => 'Login to Purchase Membership',
        'username' => 'Username',
        'username_placeholder' => 'Email or phone number',
        'password' => 'Password',
        'password_placeholder' => 'Enter password',
        'organization' => 'Organization',
        'organization_placeholder' => 'Enter organization name',
        'select_organization' => 'Select organization',
        'login_button' => 'Login',
        'logout' => 'Logout',
        'success' => 'Login successful!',
        'logged_out' => 'Logged out',
        'errors' => [
            'username_required' => 'Please enter username',
            'password_required' => 'Please enter password',
            'organization_required' => 'Please enter organization name',
            'organization_not_found' => 'Organization not found',
            'invalid_credentials' => 'Invalid username or password',
            'account_inactive' => 'Account has been locked',
            'authentication_failed' => 'Authentication failed. Please try again',
        ],
    ],

    'list' => [
        'title' => 'Select Membership Plan',
        'no_memberships' => 'No membership plans available',
        'duration' => ':days days',
        'select_button' => 'Select this plan',
        'membership_selected' => 'Selected :name plan',
        'features' => [
            'allow_comment' => 'Can comment',
            'allow_choose_seat' => 'Can choose seat',
            'allow_documentary' => 'View event documents',
        ],
        'errors' => [
            'load_failed' => 'Failed to load membership plans',
            'membership_not_found' => 'Membership plan not found',
            'selection_failed' => 'Failed to select membership plan',
        ],
    ],

    'payment' => [
        'title' => 'Payment',
        'scan_qr' => 'Scan QR code to pay',
        'amount' => 'Amount',
        'account_number' => 'Account number',
        'account_name' => 'Account name',
        'bank_name' => 'Bank',
        'expires_in' => 'Expires in',
        'waiting' => 'Waiting for payment...',
        'cancel' => 'Cancel transaction',
        'cancelled' => 'Transaction cancelled',
        'cancel_failed' => 'Failed to cancel transaction',
        'success' => 'Payment successful!',
        'failed' => 'Payment failed',
        'expired' => 'Transaction expired',
        'failed_title' => 'Payment Failed',
        'failed_message' => 'Your transaction has expired or been cancelled. Please try again.',
        'try_again' => 'Try again',
    ],

    'success' => [
        'title' => 'Purchase Successful!',
        'message' => 'Thank you for purchasing a membership. Your plan has been activated.',
        'membership_details' => 'Membership Details',
        'membership_name' => 'Plan name',
        'amount_paid' => 'Amount paid',
        'duration' => 'Duration',
        'days' => 'days',
        'notification_sent' => 'Notification has been sent to your mobile app',
        'return_home' => 'Return home',
        'purchase_another' => 'Purchase another plan',
    ],

    'notification' => [
        'title' => 'Membership Purchase Successful',
        'body' => 'You have successfully purchased :membership plan. Your plan has been activated.',
    ],
];
