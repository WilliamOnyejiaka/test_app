<?php

declare(strict_types=1);
ini_set("display_errors", 1);

require_once __DIR__ . "/../../vendor/autoload.php";
include_once __DIR__ . "/../../config/config.php";

use Lib\Router;
use Lib\Controller;
use Lib\Validator;
use Lib\Serializer;
use \Firebase\JWT\JWT;
use Lib\TokenAttributes;
use Model\Student;

$auth = new Router("auth", true);
$controller = new Controller();

$auth->post("/sign-up", fn() => $controller->public_controller(function ($body, $response) {

    $validator = new Validator();
    $validator->validate_body($body,['name','email','password']);
    [$name,$email,$password] = [$body->name,$body->email,$body->password];
    
    $validator->validate_email_with_response($email);
    $validator->validate_password_with_response($password,5);
    $password = password_hash($password,PASSWORD_DEFAULT);

    $student = new Student();
    $student_exist = (new Serializer(['email']))->tuple($student->get_student_with_email($email));

    if($student_exist){
        $response->send_response(400, [
            'error' => true,
            'message' => "email exists",
        ]);
    }

    if($student->create_student($name,$email,$password)){
        $response->send_response(200, [
            'error' => false,
            'message' => "student created successfully",
        ]);
    }

    $response->send_response(500, [
        'error' => true,
        'message' => "something went wrong",
    ]);
}));

$auth->get("/login",fn() => $controller->public_controller(function($body,$response){
    $email = $_SERVER['PHP_AUTH_USER'] ?? null;
    $password = $_SERVER['PHP_AUTH_PW'] ?? null;

    if(!$email || !$password){
        $response->send_response(400, [
            'error' => true,
            'message' => "all values needed"
        ]);
    }

    $student = new Student();
    $current_student = (new Serializer(['email','password']))->tuple($student->get_student_with_email($email));

    if($current_student){
        $valid_password = password_verify($password,$current_student['password']);
        if($valid_password){
            

            $active_user = (new Serializer([
                'id',
                'name',
                'email',
                'class',
                'image_url',
                'created_at',
                'updated_at'
            ]))->tuple($student->get_student_with_email($email));

            $token_attributes = new TokenAttributes($active_user,"students");
            $access_token = JWT::encode($token_attributes->access_token_payload(), config("secret_key"), config("hash"));
            $refresh_token = JWT::encode($token_attributes->refresh_token_payload(), config("secret_key"), config("hash"));

            $response->send_response(200, [
                'error' => false,
                'data' => [
                    "user" => $active_user,
                    'access_token' => $access_token,
                    'refresh_token' => $refresh_token
                ],
            ]);
        }

        $response->send_response(400,[
            'error' => true,
            'message' => "invalid password"
        ]);
    }

    $response->send_response(404,[
        'error' => true,
        'message' => "email does not exist"
    ]);
}));

$auth->get('/token/access-token',fn() => $controller->access_token_controller(function($payload,$body,$response){
    $student = new Student();
    $id = $payload->data->id;

    $active_user = (new Serializer(['id']))->tuple($student->get_student_with_id($id));

    if($active_user){
        $token_attributes = new TokenAttributes($active_user, "students");
        $access_token = JWT::encode($token_attributes->access_token_payload(), config("secret_key"), config("hash"));

        $response->send_response(200,[
            'error' => false,
            'token' => $access_token
        ]);
    }

    $response->send_response(404,[
        'error' => true,
        'message' => "student does not exist or has been deleted",
    ]);
}));

$auth->run();