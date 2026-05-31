<?php

return [
    'auth' => [
        'login_success'                => 'Login successful.',
        'logout_success'               => 'Logout successful.',
        'register_success'             => 'User registered successfully.',
        'invalid_credentials'          => 'Invalid credentials.',
        'token_refresh_success'        => 'Token refreshed successfully.',
        'token_refresh_error'          => 'Could not refresh the token.',
        'forgot_password_success'      => 'Password reset link sent to your email.',
        'forgot_password_error'        => 'Could not send the reset link.',
        'reset_password_success'       => 'Password reset successfully.',
        'reset_password_invalid_token' => 'Invalid reset token.',
        'reset_password_invalid_user'  => 'User not found.',
        'reset_password_throttled'     => 'Too many attempts. Please wait before trying again.',
        'reset_password_error'         => 'Could not reset the password.',
    ],

    'travel_request' => [
        'created'                   => 'Travel request created successfully.',
        'updated'                   => 'Travel request updated successfully.',
        'cancelled'                 => 'Travel request cancelled successfully.',
        'status_updated'            => 'Status updated successfully.',
        'not_found'                 => 'Travel request not found.',
        'unauthorized'              => 'You do not have permission for this action.',
        'not_editable'              => 'Cannot edit an already approved request.',
        'not_cancellable'           => 'Cannot cancel this request. The departure date has passed.',
        'already_cancelled'         => 'This request is already cancelled.',
        'already_expired'           => 'Cannot cancel an expired request.',
        'cannot_change_own_status'  => 'You cannot change the status of your own request.',
    ],

    'user' => [
        'updated'             => 'User updated successfully.',
        'deleted'             => 'User deleted.',
        'activated'           => 'User activated successfully. A new invitation has been sent by email.',
        'deactivated'         => 'User deactivated successfully.',
        'not_found'           => 'User not found.',
        'unauthorized'        => 'You do not have permission for this action.',
        'cannot_delete_self'  => 'You cannot delete your own account.',
        'cannot_disable_self' => 'You cannot deactivate your own account.',
    ],

    'invitation' => [
        'sent'                => 'Invitation sent successfully to :email.',
        'accepted'            => 'Account created successfully!',
        'not_found'           => 'Invitation not found, expired or already used.',
        'expired'             => 'This invitation has expired.',
        'already_used'        => 'This invitation has already been used.',
        'email_failed'        => 'Failed to send invitation email.',
        'email_already_exists'=> 'This email is already registered or has a pending invitation.',
    ],

    'general' => [
        'success'      => 'Operation completed successfully.',
        'server_error' => 'Internal server error.',
    ],

    'validation' => [
        'error' => 'The provided data is invalid.',
        'requester_name' => [
            'required' => 'The requester name is required.',
            'max'      => 'The requester name cannot exceed 255 characters.',
        ],
        'destination' => [
            'required' => 'The destination is required.',
        ],
        'departure_date' => [
            'required'       => 'The departure date is required.',
            'date'           => 'The departure date must be a valid date.',
            'after_or_equal' => 'The departure date must be today or in the future.',
        ],
        'return_date' => [
            'required' => 'The return date is required.',
            'date'     => 'The return date must be a valid date.',
            'after'    => 'The return date must be after the departure date.',
        ],
        'status' => [
            'required' => 'The status is required.',
            'in'       => 'Invalid status.',
        ],
        'password' => [
            'regex' => 'The password must contain an uppercase letter, a number and a symbol.',
        ],
    ],
];
