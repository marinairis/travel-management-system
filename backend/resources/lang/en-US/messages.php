<?php

return [
  // Auth Messages
  'auth' => [
    'register_success' => 'User registered successfully',
    'login_success' => 'Login successful',
    'logout_success' => 'Logout successful',
    'token_refresh_success' => 'Token refreshed successfully',
    'token_refresh_error' => 'Unable to refresh token',
    'invalid_credentials' => 'Invalid credentials',
    'forgot_password_success' => 'Password reset link sent to email',
    'forgot_password_error' => 'Error sending password reset link',
    'reset_password_success' => 'Password changed successfully',
    'reset_password_invalid_token' => 'Invalid or expired reset token',
    'reset_password_invalid_user' => 'No user found with this email',
    'reset_password_throttled' => 'Please wait before trying again',
    'reset_password_error' => 'Error changing password',
  ],

  // Validation Messages
  'validation' => [
    'error' => 'Validation error',
    'required' => 'The :attribute field is required',
    'email' => 'The :attribute field must be a valid email',
    'min' => 'The :attribute field must be at least :min characters',
    'confirmed' => 'The :attribute confirmation does not match',
    'exists' => 'The selected :attribute is invalid',
    'unique' => 'The :attribute has already been taken',
    'date' => 'The :attribute field must be a valid date',
    'after' => 'The :attribute field must be a date after :date',
    'before' => 'The :attribute field must be a date before :date',
  ],

  // Travel Request Messages
  'travel_request' => [
    'created_success' => 'Travel request created successfully',
    'updated_success' => 'Travel request updated successfully',
    'deleted_success' => 'Travel request deleted successfully',
    'status_updated_success' => 'Request status updated successfully',
    'not_found' => 'Travel request not found',
    'unauthorized' => 'You do not have permission to access this request',
    'cannot_delete_approved' => 'Cannot delete an approved request',
    'cannot_change_own_status' => 'You cannot change the status of your own request',
  ],

  // User Messages
  'user' => [
    'created_success' => 'User created successfully',
    'updated_success' => 'User updated successfully',
    'deleted_success' => 'User deleted successfully',
    'not_found' => 'User not found',
    'unauthorized' => 'You do not have permission to access this user',
    'cannot_delete_self' => 'You cannot delete your own account',
  ],

  // Activity Log Messages
  'activity_log' => [
    'created_success' => 'Activity log created successfully',
    'not_found' => 'Activity log not found',
    'unauthorized' => 'You do not have permission to access this log',
  ],

  // Location Messages
  'location' => [
    'created_success' => 'Location created successfully',
    'updated_success' => 'Location updated successfully',
    'deleted_success' => 'Location deleted successfully',
    'not_found' => 'Location not found',
    'unauthorized' => 'You do not have permission to access this location',
  ],

  // General Messages
  'general' => [
    'success' => 'Operation completed successfully',
    'error' => 'Operation error',
    'server_error' => 'Server error',
    'unauthorized' => 'Unauthorized',
    'forbidden' => 'Access denied',
    'not_found' => 'Resource not found',
    'validation_error' => 'Validation error',
    'internal_error' => 'Internal server error',
  ],

  // Field Names
  'fields' => [
    'name' => 'name',
    'email' => 'email',
    'password' => 'password',
    'password_confirmation' => 'password confirmation',
    'requester_name' => 'requester name',
    'destination' => 'destination',
    'departure_date' => 'departure date',
    'return_date' => 'return date',
    'notes' => 'notes',
    'status' => 'status',
    'is_admin' => 'administrator',
    'user_id' => 'user',
    'approved_by' => 'approved by',
  ],
];
