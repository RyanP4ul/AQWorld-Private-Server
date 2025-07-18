<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class ItemSkillController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "items_skills",
            reqAccess: 60,
            title: "Item Skills",
            viewTemplate: "panel/items.skills.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "ItemID" => ["required", 'int', "min:1"],
                "SkillID" => ["required", "int", "min:1"]
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $itemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :Id", [":Id", $item["ItemID"], PDO::PARAM_INT]);
            $skillObj = DB::Connection()->First("SELECT Name FROM skills WHERE id = :Id", [":Id", $item["SkillID"], PDO::PARAM_INT]);

            $item["ItemID"] = $itemObj == null ? $item["ItemID"] : "[" . $item["ItemID"] . "] " . $itemObj["Name"];
            $item["SkillID"] = $skillObj == null ? $item["SkillID"] : "[" . $item["SkillID"] . "] " . $skillObj["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }


    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Count("SELECT COUNT(*) FROM items WHERE id = :Id", [":Id", $_POST["ItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID does not exists!"]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM skills WHERE id = :Id", [":Id", $_POST["SkillID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["SkillID" => ["SkillID does not exists!"]]], 403);
        }

        if ($_GET["type"] != "update" && DB::Connection()->Count("SELECT COUNT(*) FROM items_skills WHERE ItemID = :ItemID AND SkillID = :SkillID", [
                [":ItemID", $_POST["ItemID"], PDO::PARAM_INT],
                [":SkillID", $_POST["SkillID"], PDO::PARAM_INT]
            ]) > 0)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID and SkillID are already exists."]]], 403);
        }

        return $data;
    }

}