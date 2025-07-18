<?php

namespace App\Controllers\Panel;

use App\config;
use Exception;

class UploadController extends PanelController
{

    private const WHITE_LISTS_FORMAT = ["swf"];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    public function index() : void
    {
        $this->view("panel/upload.html.twig", [
            "title" => "Upload Files"
        ]);
    }

    public function getFilePath(string $type) : string
    {
        return match (strtolower($type)) {
            "male" => "classes/M/",
            "female" => "classes/F/",
            "maps" => "maps/",
            "monsters" => "mon/",
            default => "items/" . $type . "/",
        };
    }

    public function Upload() : void
    {
        if (!$this->isAjax() || !isset($_FILES["file"], $_POST["type"])) $this->abort(404);

        try {
            header('Content-Type: application/json');

            $type = $_POST["type"];

            if (empty($type)) $this->response_json(["msg" => "Type Empty!"], 403);

            $file = $_FILES["file"];
            $gameDir = realpath(__DIR__ . '/../../../') . "/gamefiles/" . $this->getFilePath($type);

            if ($file["error"] !== UPLOAD_ERR_OK) $this->response_json(["msg" => "File upload Error!"], 403);
            if ($file["size"] > self::MAX_FILE_SIZE) $this->response_json(["msg" => "File is too large. Maximum size is 5MB"], 403);
            if (!is_writable($gameDir)) $this->response_json(["msg" => "Upload directory is not writable!"], 403);

            $originalName = basename($file["name"]);

            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, self::WHITE_LISTS_FORMAT)) $this->response_json(["msg" => "Invalid file type!"], 403);

            $baseName = preg_replace("/[^a-zA-Z0-9_-]/", "_", pathinfo($originalName, PATHINFO_FILENAME));
            $finalName = $baseName . '.' . $extension;

            $this->RenameDuplicateFile($baseName, $finalName, $gameDir, $extension);

            if (!move_uploaded_file($file['tmp_name'], $gameDir . $finalName)) $this->response_json(["msg" => "Failed to move uploaded file."], 403);

            $this->response_json(["msg" => "File uploaded successfully as " . htmlspecialchars($finalName)]);
        } catch (Exception $exception) {
            $this->response_json(["msg" => $exception->getMessage() ], 403);
        }
    }


    public function RenameDuplicateFile(string $baseName, string $finalName, string $gameDir, string $extension) : void
    {
        $counter = 1;
        while (file_exists($gameDir . $finalName)) {
            $renamedOld = $baseName . '_' . $counter . '.' . $extension;
            if (!file_exists($gameDir . $renamedOld)) {
                rename($gameDir . $finalName, $gameDir . $renamedOld);
                break;
            }
            $counter++;
        }
    }



}