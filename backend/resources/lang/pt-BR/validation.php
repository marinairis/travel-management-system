<?php

return [
  'required' => 'O campo :attribute é obrigatório.',
  'email' => 'O campo :attribute deve ser um endereço de email válido.',
  'min' => [
    'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
  ],
  'confirmed' => 'A confirmação do campo :attribute não confere.',
  'exists' => 'O :attribute selecionado é inválido.',
  'unique' => 'O :attribute já está sendo usado.',
  'date' => 'O campo :attribute deve ser uma data válida.',
  'after' => 'O campo :attribute deve ser uma data posterior a :date.',
  'before' => 'O campo :attribute deve ser uma data anterior a :date.',
  'string' => 'O campo :attribute deve ser uma string.',
  'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
  'in' => 'O :attribute selecionado é inválido.',
  'max' => [
    'string' => 'O campo :attribute não pode ser maior que :max caracteres.',
  ],

  'attributes' => [
    'name' => 'nome',
    'email' => 'email',
    'password' => 'senha',
    'password_confirmation' => 'confirmação de senha',
    'requester_name' => 'nome do solicitante',
    'destination' => 'destino',
    'departure_date' => 'data de ida',
    'return_date' => 'data de volta',
    'notes' => 'observações',
    'status' => 'status',
    'is_admin' => 'administrador',
    'user_id' => 'usuário',
    'approved_by' => 'aprovado por',
  ],
];
