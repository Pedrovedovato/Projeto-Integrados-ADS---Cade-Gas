<?php
//Mock teste controler x rotas

class AuthController {

    public function register() {
        echo json_encode([
            "endpoint" => "POST /register",
            "mensagem" => "Cadastro funcionando (mock)",
            "status" => "ok"
        ]);
    }

    public function login() {
        echo json_encode([
            "endpoint" => "POST /login",
            "mensagem" => "Login funcionando (mock)",
            "status" => "ok"
        ]);
    }
}