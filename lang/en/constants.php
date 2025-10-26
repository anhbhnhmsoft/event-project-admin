<?php

return [
    'common_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
    'event_seat_status' => [
        'available' => 'Available',
        'booked' => 'Booked',
    ],
    'event_status' => [
        'active' => 'Active',
        'upcoming' => 'Upcoming',
        'closed' => 'Closed',
    ],
    'event_user_history_status' => [
        'seened' => 'Viewed ~ Not Paid',
        'seened_short' => 'Viewed',
        'booked' => 'Booked',
        'participated' => 'Participated',
        'cancelled' => 'Cancelled',
    ],
    'transaction_status' => [
        'waiting' => 'Waiting',
        'success' => 'Success',
        'failed' => 'Failed',
        'unknown' => 'Unknown',
    ],
    'transaction_type' => [
        'membership' => 'Buy Membership Package',
        'plan_service' => 'Buy Service Package',
        'buy_document' => 'Buy Event Document',
        'buy_comment' => 'Buy Comment Permission',
        'event_seat' => 'Event Seat Payment',
    ],
    'role_user' => [
        'super_admin' => 'Super Admin',
        'admin' => 'Administrator',
        'customer' => 'Customer',
        'speaker' => 'Speaker',
        'unknown' => 'Unknown',
    ],
    'language' => [
        'vi' => 'Vietnamese',
        'en' => 'English',
    ],
    'membership_type' => [
        'for_customer' => 'Package for participants',
        'for_organizer' => 'Package for organizers',
    ],
    'membership_user_status' => [
        'inactive' => 'Inactive',
        'active' => 'Active',
        'expired' => 'Expired',
    ],
    'user_notification_status' => [
        'pending' => 'Pending',
        'sent' => 'Sent',
        'read' => 'Read',
        'failed' => 'Failed',
    ],
    'user_notification_type' => [
        'event_reminder' => 'Event Reminder',
        'event_invitation' => 'Event Invitation',
        'event_cancelled' => 'Event Cancelled',
        'event_updated' => 'Event Updated',
        'membership_approved' => 'Membership Approved',
        'system_announcement' => 'System Announcement',
        'membership_expire_reminder' => 'Membership Expire Reminder',
    ],
    'config_membership' => [
        'allow_comment' => 'Allow Comments',
        'allow_choose_seat' => 'Allow Seat Selection',
        'allow_documentary' => 'Allow View/Download Documents in Event',
        'limit_event' => 'Event Limit',
        'limit_member' => 'Member Participation Limit',
        'feature_poll' => 'Poll/Survey Feature Before or After Event',
        'feature_game' => 'Game Feature in Event',
        'feature_comment' => 'Comment Feature in Event',
    ],
    'event_document_user_status' => [
        'inactive' => 'Viewed',
        'payment_pending' => 'Payment Pending',
        'active' => 'Paid & Owned',
    ],
    'event_comment_type' => [
        'public' => 'Public Comment',
        'private' => 'Private Comment',
    ],
    'config_type' => [
        'image' => 'Image',
        'string' => 'String',
    ],
    'event_user_role' => [
        'organizer' => 'Organizer',
        'presenter' => 'Presenter',
    ],
    'event_game_type' => [
        'lucky_spin' => 'Lucky Spin',
    ],
    'question_type' => [
        'multiple' => 'Multiple Choice',
        'open_ended' => 'Open-ended Answer',
        'unknown' => 'Unknown',
    ],
    'type_send_notification' => [
        'some_users' => 'Select Users',
        'all_users' => 'Broadcast (All Users)',
    ],
    'unit_duration_type' => [
        'minute' => 'Minute',
        'hour' => 'Hour',
        'day' => 'Day',
    ],
];
