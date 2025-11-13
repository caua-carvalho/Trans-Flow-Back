<?php
/**
 * Classe auxiliar para padronizar respostas da API
 */

class Response {
    public static function success($data = null, $message = "Operação realizada com sucesso") {
        http_response_code(200);
        return json_encode([
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
    }

    public static function created($data = null, $message = "Recurso criado com sucesso") {
        http_response_code(201);
        return json_encode([
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
    }

    public static function error($error, $code = 400) {
        http_response_code($code);
        return json_encode([
            'success' => false,
            'error' => $error
        ]);
    }

    public static function notFound($error = "Recurso não encontrado") {
        http_response_code(404);
        return json_encode([
            'success' => false,
            'error' => $error
        ]);
    }

    public static function serverError($error = "Erro interno do servidor") {
        http_response_code(500);
        return json_encode([
            'success' => false,
            'error' => $error
        ]);
    }
}
?>
