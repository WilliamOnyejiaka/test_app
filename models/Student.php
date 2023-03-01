<?php
declare(strict_types=1);
namespace Model;
ini_set("display_errors", 1);

require_once __DIR__ . "/../vendor/autoload.php";

use Model\Model;

class Student extends Model
{

    public function __construct(){
        parent::__construct();
        $this->tbl_name = "students";
    }

    public function create_student(string $name,string $email,string $password){
        $query = "INSERT INTO $this->tbl_name(name,email,password) VALUES(?,?,?)";
        $stmt = $this->connection->prepare($query);

        $name = htmlspecialchars(strip_tags($name));
        $email = htmlspecialchars(strip_tags($email));
        $password = htmlspecialchars(strip_tags($password));

        $stmt->bind_param("sss",$name,$email,$password);
        return $stmt->execute() ?? false;
    }

    public function get_student_with_email(string $email){
        $query = "SELECT * FROM $this->tbl_name WHERE email = ?";
        $stmt = $this->connection->prepare($query);

        $email = htmlspecialchars(strip_tags($email));

        $stmt->bind_param("s",$email);
        $executed = $stmt->execute() ? true : false;
        $this->execution_error($executed);
        return $stmt->get_result();
    }

    public function get_student_with_id(int $id)
    {
        $query = "SELECT * FROM $this->tbl_name WHERE id = ?";
        $stmt = $this->connection->prepare($query);

        $stmt->bind_param("i", $id);
        $executed = $stmt->execute() ? true : false;
        $this->execution_error($executed);
        return $stmt->get_result();
    }

    public function update_image_name(int $id,string $image_name){
        $query = "UPDATE $this->tbl_name SET image_name = ? WHERE id = ?";
        $stmt = $this->connection->prepare($query);

        $image_name = htmlspecialchars(strip_tags($image_name));

        $stmt->bind_param("si",$image_name,$id);
        return $stmt->execute() ?? false;
    }

}