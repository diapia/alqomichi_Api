<?php

namespace Core;

class Request
{
    public static function body(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            Response::error('El cuerpo de la solicitud debe ser un JSON válido.', 400);
        }

        return $data;
    }
}
