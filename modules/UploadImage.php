<?php
declare(strict_types=1);

namespace Module;

ini_set("display_errors", 1);


class UploadImage
{
    private $image_file;
    private string $upload_folder_path;

    public function __construct($image_file, $upload_folder_path)
    {
        $this->image_file = $image_file;
        $this->upload_folder_path = $upload_folder_path;
    }

    private function create_image_name()
    {
        $extension = pathinfo($this->image_file['name'], PATHINFO_EXTENSION);
        return time() . "." . $extension;
    }

    public function upload_image()
    {
        $image_name = $this->create_image_name();
        return move_uploaded_file($this->image_file['tmp_name'], "$this->upload_folder_path/$image_name") ? $image_name : false;
    }
}