<?php

declare(strict_types=1);
ini_set("display_errors", 1);

require_once __DIR__ . "/../../vendor/autoload.php";
include_once __DIR__ . "/../../config/config.php";

use Lib\Router;
use Lib\Controller;
use Lib\Validator;
use Lib\Serializer;
use Module\UploadImage;
use Model\Student;

$student = new Router("student", true);
$controller = new Controller();

$student->post("/upload-image", fn() => $controller->protected_controller(function ($payload, $body, $response) {
    $image_file_exists = $_FILES['image_file'] ?? false;

    (!$image_file_exists) && $response->send_response(404, [
        'error' => true,
        'message' => "image file missing"
    ]);

    $image_name = (new UploadImage($_FILES['image_file'], "./../../images"))->upload_image();

    (!$image_name) && $response->send_response(500, [
        'error' => true,
        'message' => "something went wrong"
    ]);

    $id = $payload->data->id;
    $studentDB = new Student();

    if($studentDB->update_image_name($id,$image_name)){
        $response->send_response(404, [
            'error' => false,
            'message' => "image added successfully"
        ]);
    }

    $response->send_response(500, [
        'error' => true,
        'message' => "something went wrong"
    ]);
}));

$student->get('/get-student-image',fn() => $controller->protected_controller(function($payload,$body,$response){
    $id = $payload->data->id;
    $student = new Student();
    $image_name = (new Serializer(['image_name']))->tuple($student->get_student_with_id($id))['image_name'];

    $base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https" :  "http" ;
    $base_url .= "://". $_SERVER['HTTP_HOST'];

    $image_url = $base_url . "/Student-Management-System/images/$image_name";
    header("Location: $image_url",true,301);
    exit();
}));

$student->run();

