<?php

return [
    'common_success' => [
        'get_success' => 'Get data successfully',
        'add_success' => 'Add data successfully',
        'update_success' => 'Update data successfully',
    ],
    'common_error' => [
        'server_error' => 'A server error has occurred, please contact the administrator for support.',
        'request_error' => 'No response received from the server, please contact the administrator for support.',
        'program_error' => 'A program error has occurred, please contact the administrator for support.',
        'unknown_error' => 'An error has occurred, please contact the administrator for support.',
        'invalid_or_expired_token' => 'Your account has expired or is invalid. Please log in again.',
        'api_not_found' => 'An unknown source error has occurred, please contact the administrator for support.',
        'method_not_allowed' => 'API is not in the correct format, please contact the administrator for support.',
        'permission_error' => 'You do not have permission to perform this action, please contact the administrator for support.',
        'authorization_header_not_found' => 'Authorization header not found, please contact the administrator for support.',
        'refresh_token_fail' => 'Your account has expired or is invalid. Please log in again.',
        'data_not_found' => 'Data is empty, please try again later.',
        'data_not_fields' => 'Some data has not been filled in, please try again.',
        'data_exists' => 'Data already exists, please try again.',
        'validation_failed' => 'Data is not valid.',
        'vm_error_general' => 'VM is currently unavailable, please contact admin',
        'project_unauthorized' => 'Your project or VM has authentication issues, please check again later',
        'copy_error' => 'Error when copying'
    ],
    'validation' => [
       'status_integer' => 'Status must be an integer.',
        'status_in' => 'Status is not valid.',
        'notification_type_integer' => 'Notification type must be an integer.',
        'notification_type_in' => 'Notification type is not valid.',
        'notification_ids_required' => 'Notification IDs are required.',
        'notification_ids_array' => 'Notification IDs must be an array.',
        'notification_ids_min' => 'Notification IDs must have at least 1 element.',
        'notification_id_required' => 'Notification ID is required.',
        'notification_id_integer' => 'Notification ID must be an integer.',
        'notification_id_exists' => 'Notification ID does not exist.',
    ],
    'mark_as_read_success' => 'Mark as read successfully.',
    'push_token_saved' => 'Push token saved successfully',
];
