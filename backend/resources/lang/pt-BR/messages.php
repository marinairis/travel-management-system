<?php

return [
    'auth' => [
        'login_success'                => 'Login realizado com sucesso.',
        'logout_success'               => 'Logout realizado com sucesso.',
        'register_success'             => 'Usuário registrado com sucesso.',
        'invalid_credentials'          => 'Credenciais inválidas.',
        'token_refresh_success'        => 'Token renovado com sucesso.',
        'token_refresh_error'          => 'Não foi possível renovar o token.',
        'forgot_password_success'      => 'Link de redefinição enviado para o seu e-mail.',
        'forgot_password_error'        => 'Não foi possível enviar o link de redefinição.',
        'reset_password_success'       => 'Senha redefinida com sucesso.',
        'reset_password_invalid_token' => 'Token de redefinição inválido.',
        'reset_password_invalid_user'  => 'Usuário não encontrado.',
        'reset_password_throttled'     => 'Muitas tentativas. Aguarde antes de tentar novamente.',
        'reset_password_error'         => 'Não foi possível redefinir a senha.',
    ],

    'travel_request' => [
        'created'                   => 'Pedido de viagem criado com sucesso',
        'updated'                   => 'Pedido de viagem atualizado com sucesso',
        'cancelled'                 => 'Pedido cancelado com sucesso',
        'status_updated'            => 'Status atualizado com sucesso',
        'not_found'                 => 'Pedido de viagem não encontrado',
        'unauthorized'              => 'Você não tem permissão para esta ação',
        'not_editable'              => 'Não é possível editar um pedido já aprovado',
        'not_cancellable'           => 'Não é possível cancelar um pedido aprovado cuja data de partida já passou',
        'already_cancelled'         => 'Este pedido já está cancelado',
        'already_expired'           => 'Não é possível cancelar um pedido vencido',
        'cannot_change_own_status'  => 'Você não pode alterar o status do seu próprio pedido',
    ],

    'user' => [
        'updated'             => 'Usuário atualizado com sucesso',
        'deleted'             => 'Usuário excluído com sucesso',
        'activated'           => 'Usuário ativado com sucesso. Um novo convite foi enviado por e-mail.',
        'deactivated'         => 'Usuário desativado com sucesso',
        'not_found'           => 'Usuário não encontrado',
        'unauthorized'        => 'Você não tem permissão para esta ação',
        'cannot_view'         => 'Você não tem permissão para visualizar este usuário',
        'cannot_delete_self'  => 'Você não pode excluir sua própria conta',
        'cannot_disable_self' => 'Você não pode desativar sua própria conta',
    ],

    'invitation' => [
        'sent'                => 'Convite enviado com sucesso para :email.',
        'accepted'            => 'Conta criada com sucesso!',
        'not_found'           => 'Convite não encontrado, expirado ou já utilizado.',
        'expired'             => 'Este convite expirou.',
        'already_used'        => 'Este convite já foi utilizado.',
        'email_failed'        => 'Falha ao enviar e-mail de convite.',
        'email_already_exists'=> 'Este e-mail já está cadastrado ou possui um convite pendente.',
    ],

    'general' => [
        'success'      => 'Operação realizada com sucesso.',
        'server_error' => 'Erro interno do servidor.',
    ],

    'validation' => [
        'error' => 'Os dados informados são inválidos.',
        'requester_name' => [
            'required' => 'O nome do solicitante é obrigatório.',
            'max'      => 'O nome do solicitante não pode ter mais de 255 caracteres.',
        ],
        'destination' => [
            'required' => 'O destino é obrigatório.',
        ],
        'departure_date' => [
            'required'      => 'A data de partida é obrigatória.',
            'date'          => 'A data de partida deve ser uma data válida.',
            'after_or_equal'=> 'A data de partida deve ser hoje ou no futuro.',
        ],
        'return_date' => [
            'required' => 'A data de retorno é obrigatória.',
            'date'     => 'A data de retorno deve ser uma data válida.',
            'after'    => 'A data de retorno deve ser após a data de partida.',
        ],
        'reason' => [
            'string' => 'O motivo deve ser um texto.',
            'max'    => 'O motivo não pode ter mais de 500 caracteres.',
        ],
        'status' => [
            'required' => 'O status é obrigatório.',
            'in'       => 'Status inválido.',
        ],
        'password' => [
            'regex' => 'A senha deve conter letra maiúscula, número e símbolo.',
        ],
    ],
];
