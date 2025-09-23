<?php

return [
    'success' => [
        'get_success' => 'Get data successfully',
        'filter_success' => 'Filter successfully',
    ],
    'error' => [
        'get_failed' => 'Get data failed: :error',
        'filter_failed' => 'Filter failed',
        'Event_not_to_organizer' => 'Event does not belong to your organizer',
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
    ],
];
