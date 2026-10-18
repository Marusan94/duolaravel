<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser una cadena de texto.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'lowercase' => 'El campo :attribute debe estar en minúsculas.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'unique' => 'El valor del campo :attribute ya está en uso.',
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'max' => [
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
    ],
];
