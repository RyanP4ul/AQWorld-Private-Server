<?php
namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class EnhancementController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "enhancements",
            reqAccess: 40,
            title: "Enhancements",
            viewTemplate: "panel/enhancement.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "Name" => ["required", "string", "max:32"],
                "PatternID" => ["required", "int", "min:1", "max:100"],
                "Rarity" => ["required", "int"],
                "DPS" => ["required", "int"],
                "Level" => ["required", "int"]
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item)
        {
            $pattern = DB::Connection()->First("SELECT Name FROM enhancements_patterns WHERE id = :Id", [":Id", $item["PatternID"], PDO::PARAM_INT]);

            $item["PatternID"] = $pattern == null ? $item["PatternID"] : "[" . $item["PatternID"] . "] " . $pattern["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("enhancements", "Name", $_POST["Name"]))
        {
            $this->response_json(["msg" => ["Name" => [$_POST["Name"] . " Name has already been taken."]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM enhancements_patterns WHERE id = :Id", [":Id", $_POST["PatternID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["PatternID" => ["PatternID does not exists!"]]], 403);
        }

        return $data;
    }

}