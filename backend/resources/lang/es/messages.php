<?php

return [
    'auth' => [
        'login_success'                => 'Inicio de sesión exitoso.',
        'logout_success'               => 'Sesión cerrada exitosamente.',
        'register_success'             => 'Usuario registrado exitosamente.',
        'invalid_credentials'          => 'Credenciales inválidas.',
        'token_refresh_success'        => 'Token renovado exitosamente.',
        'token_refresh_error'          => 'No se pudo renovar el token.',
        'forgot_password_success'      => 'Enlace de restablecimiento enviado a su correo.',
        'forgot_password_error'        => 'No se pudo enviar el enlace de restablecimiento.',
        'reset_password_success'       => 'Contraseña restablecida exitosamente.',
        'reset_password_invalid_token' => 'Token de restablecimiento inválido.',
        'reset_password_invalid_user'  => 'Usuario no encontrado.',
        'reset_password_throttled'     => 'Demasiados intentos. Espere antes de intentarlo de nuevo.',
        'reset_password_error'         => 'No se pudo restablecer la contraseña.',
    ],

    'travel_request' => [
        'created'                   => 'Solicitud de viaje creada exitosamente.',
        'updated'                   => 'Solicitud de viaje actualizada exitosamente.',
        'cancelled'                 => 'Solicitud de viaje cancelada exitosamente.',
        'status_updated'            => 'Estado actualizado exitosamente.',
        'not_found'                 => 'Solicitud de viaje no encontrada.',
        'unauthorized'              => 'No tiene permiso para esta acción.',
        'not_editable'              => 'No se puede editar una solicitud ya aprobada.',
        'not_cancellable'           => 'No se puede cancelar esta solicitud. La fecha de salida ya pasó.',
        'already_cancelled'         => 'Esta solicitud ya está cancelada.',
        'already_expired'           => 'No se puede cancelar una solicitud vencida.',
        'cannot_change_own_status'  => 'No puede cambiar el estado de su propia solicitud.',
    ],

    'user' => [
        'updated'             => 'Usuario actualizado exitosamente.',
        'deleted'             => 'Usuario eliminado.',
        'activated'           => 'Usuario activado exitosamente. Se ha enviado una nueva invitación por correo.',
        'deactivated'         => 'Usuario desactivado exitosamente.',
        'not_found'           => 'Usuario no encontrado.',
        'unauthorized'        => 'No tiene permiso para esta acción.',
        'cannot_delete_self'  => 'No puede eliminar su propia cuenta.',
        'cannot_disable_self' => 'No puede desactivar su propia cuenta.',
    ],

    'invitation' => [
        'sent'                => 'Invitación enviada exitosamente a :email.',
        'resent'              => 'Invitación reenviada exitosamente a :email.',
        'accepted'            => '¡Cuenta creada exitosamente!',
        'not_found'           => 'Invitación no encontrada, expirada o ya utilizada.',
        'expired'             => 'Esta invitación ha expirado.',
        'already_used'        => 'Esta invitación ya fue utilizada.',
        'email_failed'        => 'Error al enviar el correo de invitación.',
        'email_already_exists'=> 'Este correo ya está registrado o tiene una invitación pendiente.',
    ],

    'general' => [
        'success'      => 'Operación realizada exitosamente.',
        'server_error' => 'Error interno del servidor.',
    ],

    'validation' => [
        'error' => 'Los datos proporcionados son inválidos.',
        'requester_name' => [
            'required' => 'El nombre del solicitante es obligatorio.',
            'max'      => 'El nombre del solicitante no puede exceder 255 caracteres.',
        ],
        'destination' => [
            'required' => 'El destino es obligatorio.',
        ],
        'departure_date' => [
            'required'       => 'La fecha de salida es obligatoria.',
            'date'           => 'La fecha de salida debe ser una fecha válida.',
            'after_or_equal' => 'La fecha de salida debe ser hoy o en el futuro.',
        ],
        'return_date' => [
            'required' => 'La fecha de retorno es obligatoria.',
            'date'     => 'La fecha de retorno debe ser una fecha válida.',
            'after'    => 'La fecha de retorno debe ser posterior a la fecha de salida.',
        ],
        'status' => [
            'required' => 'El estado es obligatorio.',
            'in'       => 'Estado inválido.',
        ],
        'password' => [
            'regex' => 'La contraseña debe contener una letra mayúscula, un número y un símbolo.',
        ],
    ],
];
