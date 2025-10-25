<?php

return [
  'required' => 'The :attribute field is required.',
  'email' => 'The :attribute field must be a valid email address.',
  'min' => [
    'string' => 'The :attribute field must be at least :min characters.',
  ],
  'confirmed' => 'The :attribute confirmation does not match.',
  'exists' => 'The selected :attribute is invalid.',
  'unique' => 'The :attribute has already been taken.',
  'date' => 'The :attribute field must be a valid date.',
  'after' => 'The :attribute field must be a date after :date.',
  'before' => 'The :attribute field must be a date before :date.',
  'string' => 'The :attribute field must be a string.',
  'boolean' => 'The :attribute field must be true or false.',
  'in' => 'The selected :attribute is invalid.',
  'max' => [
    'string' => 'The :attribute field may not be greater than :max characters.',
  ],

  'attributes' => [
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
