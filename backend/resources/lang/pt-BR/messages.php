<?php

return [
  // Auth Messages
  'auth' => [
    'register_success' => 'Usuário registrado com sucesso',
    'login_success' => 'Login realizado com sucesso',
    'logout_success' => 'Logout realizado com sucesso',
    'token_refresh_success' => 'Token renovado com sucesso',
    'token_refresh_error' => 'Não foi possível renovar o token',
    'invalid_credentials' => 'Credenciais inválidas',
    'forgot_password_success' => 'Link de recuperação enviado para o email',
    'forgot_password_error' => 'Erro ao enviar link de recuperação',
    'reset_password_success' => 'Senha alterada com sucesso',
    'reset_password_invalid_token' => 'Token de recuperação inválido ou expirado',
    'reset_password_invalid_user' => 'Não encontramos um usuário com este email',
    'reset_password_throttled' => 'Por favor, aguarde antes de tentar novamente',
    'reset_password_error' => 'Erro ao alterar senha',
  ],

  // Validation Messages
  'validation' => [
    'error' => 'Erro de validação',
    'required' => 'O campo :attribute é obrigatório',
    'email' => 'O campo :attribute deve ser um email válido',
    'min' => 'O campo :attribute deve ter pelo menos :min caracteres',
    'confirmed' => 'A confirmação do campo :attribute não confere',
    'exists' => 'O :attribute selecionado é inválido',
    'unique' => 'O :attribute já está em uso',
    'date' => 'O campo :attribute deve ser uma data válida',
    'after' => 'O campo :attribute deve ser uma data posterior a :date',
    'before' => 'O campo :attribute deve ser uma data anterior a :date',
  ],

  // Travel Request Messages
  'travel_request' => [
    'created_success' => 'Pedido de viagem criado com sucesso',
    'updated_success' => 'Pedido de viagem atualizado com sucesso',
    'deleted_success' => 'Pedido de viagem excluído com sucesso',
    'status_updated_success' => 'Status do pedido atualizado com sucesso',
    'not_found' => 'Pedido de viagem não encontrado',
    'unauthorized' => 'Você não tem permissão para acessar este pedido',
    'cannot_delete_approved' => 'Não é possível excluir um pedido aprovado',
    'cannot_change_own_status' => 'Você não pode alterar o status do seu próprio pedido',
  ],

  // User Messages
  'user' => [
    'created_success' => 'Usuário criado com sucesso',
    'updated_success' => 'Usuário atualizado com sucesso',
    'deleted_success' => 'Usuário excluído com sucesso',
    'not_found' => 'Usuário não encontrado',
    'unauthorized' => 'Você não tem permissão para acessar este usuário',
    'cannot_delete_self' => 'Você não pode excluir sua própria conta',
  ],

  // Activity Log Messages
  'activity_log' => [
    'created_success' => 'Log de atividade criado com sucesso',
    'not_found' => 'Log de atividade não encontrado',
    'unauthorized' => 'Você não tem permissão para acessar este log',
  ],

  // Location Messages
  'location' => [
    'created_success' => 'Localização criada com sucesso',
    'updated_success' => 'Localização atualizada com sucesso',
    'deleted_success' => 'Localização excluída com sucesso',
    'not_found' => 'Localização não encontrada',
    'unauthorized' => 'Você não tem permissão para acessar esta localização',
  ],

  // General Messages
  'general' => [
    'success' => 'Operação realizada com sucesso',
    'error' => 'Erro na operação',
    'server_error' => 'Erro no servidor',
    'unauthorized' => 'Não autorizado',
    'forbidden' => 'Acesso negado',
    'not_found' => 'Recurso não encontrado',
    'validation_error' => 'Erro de validação',
    'internal_error' => 'Erro interno do servidor',
  ],

  // Field Names
  'fields' => [
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
