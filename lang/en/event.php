<?php

return [
    'success' => [
        'get_success' => 'Get data successfully',
        'filter_success' => 'Filter successfully',
        'congratulartion_prize' => '🎉 Congratulations on your gift!',
        'congratulartion_desc' => 'You have won the gift :gift_name in the game :game.',
        'notification_title_mbs_near'   => 'Early Expiry Notice',
        'notification_desc_mbs_near'   => 'Your membership will expire in 7 days. Please renew to continue using the service!',
        'notification_title_mbs_expired'   => 'Expiration Warning!',
        'notification_desc_mbs_expired'   => 'Your membership will expire in the next 24 hours. Please renew now to avoid service interruption.',
        'notification_title_event_start' => 'Event :name has started!',
        'notification_desc_event_start' => 'The event you registered for has started. Join now!',
        'success' => 'Success',
        'payment_success' => 'Payment successful',
    ],
    'error' => [
        'get_failed' => 'Get data failed: :error',
        'filter_failed' => 'Filter failed',
        'Event_not_to_organizer' => 'Event does not belong to your organizer',
        'payment_seat_required' => 'Please pay to book a seat',
    ],
    'validation' => [
        'event_id_required' => 'Event ID is required',
        'event_id_integer' => 'Event ID must be an integer',
        'event_id_exists' => 'Event does not exist',
        'event_seat_id_required' => 'Event seat ID is required',
        'event_seat_id_integer' => 'Event seat ID must be an integer',
        'event_seat_id_exists' => 'Event seat does not exist',
        'seat_not_in_event' => 'Seat does not belong to this event',
        'seat_taken' => 'Seat has already been taken',
        'no_available_seat' => 'No available seats',
        'seat_permission_denied' => 'Your membership does not allow choosing seats',
        'status_required' => 'Status is required',
        'status_integer' => 'Status must be an integer',
        'status_exists' => 'Status does not exist',
        'history_id_required' => 'History ID is required when booking',
        'already_booked' => 'You have already booked',
        // BỔ SUNG: Thiếu document_id
        'document_id_required' => 'Document ID is required',
        'document_id_exits' => 'Document does not exist',
        'seat_already_booked' => 'Seat has already been booked',
        'seat_not_belong_to_event' => 'Seat does not belong to this event',
        'cannot_assign_seat' => 'Cannot assign seat',
        'cannot_cancel_seat' => 'Cannot cancel seat',
        'free_to_join' => 'Free to join',
        'seat_not_found' => 'Seat not found',
        'area_not_found' => 'Area not found',
        'cannot_create_ticket' => 'Cannot create ticket',
        'seat_payment_description' => 'Seat :seat_code - :event_id',
        'register_fail_title' => 'Registration failed'
    ],
    // BỔ SUNG: General
    'general' => [
        'event_title' => 'Event',
        'create_event' => 'Create new event',
        'edit_event' => 'Edit',
        'delete' => 'Delete',
        'save_changes' => 'Save changes',
        'delete_file_success' => 'Attachment file deleted',
        'delete_file_error' => 'Error deleting attachment file',
        'update_success' => 'Update successful',
        'update_failure' => 'Update failed',
        'open_link' => 'Open page',
    ],
    // BỔ SUNG: Pages
    'pages' => [
        'list_title' => 'Event list',
        'create_title' => 'Create new event',
        'edit_title' => 'Edit event',
        'comments_title' => 'Comments',
        'games_title' => 'Games',
        'votes_title' => 'Polls & Quizzes',
        'seats_title' => 'Area & Seat Management',
        'seats_nav_label' => 'Seating chart',
    ],
    // BỔ SUNG: Comments
    'comments' => [
        'model_label' => 'Comment',
        'plural_model_label' => 'Comments',
        'user_column' => 'User',
        'content_column' => 'Content',
        'time_column' => 'Comment time',
        'view_action' => 'View',
        'delete_action' => 'Delete',
        'record_title' => 'comment',
        'delete_success' => 'Comment deleted successfully!',
    ],
    // BỔ SUNG: Votes
    'votes' => [
        'options_label' => 'Options / Answers',
        'option_content_label' => 'Option content',
        'option_order_label' => 'Order',
        'option_is_correct_label' => 'Correct Answer (for quiz)',
        'option_is_correct_helper' => 'Mark if this is the correct answer',
        'add_option_action' => 'Add option',
        'options_helper_text' => 'Minimum 2 options for multiple-choice questions',
        'add_question_action' => 'Add question',
        'new_question_label' => 'New question',
        'questions_label' => 'Questions',
    ],
    'mail' => [
        'subject_event_start' => 'Event :name has started!',
    ],
    'messages' =>
    [
        'register_success_title' => 'Successful Registration & Ticket Issuance!',
        'new_user_new_ticket_message' => 'Welcome! We have successfully created an account and issued a ticket. Your login information (default password is your phone number) and ticket have been sent to your email. Please check your inbox for details.',

        'ticket_granted_title' => 'Successful Ticket Issuance!',
        'existing_user_new_ticket_message' => 'Your account has been issued a ticket to this event. Please check your email for the ticket code and detailed instructions about the event.',

        'ticket_confirmed_title' => 'Ticket Confirmed!',
        'existing_user_existing_ticket_message' => 'The system has confirmed that you have an account and a ticket to this event. Detailed information about the event will be sent back to your email. Please check your mailbox.',
    ]
];
