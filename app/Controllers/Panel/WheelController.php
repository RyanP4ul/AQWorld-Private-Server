<?php

namespace App\Controllers\Panel;

use Database\DB;

class WheelController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "wheels",
            reqAccess: 40,
            title: "Wheels",
            viewTemplate: "panel/wheels.html.twig",
            validationRules: [
                "id" => ["required", "int", "min:1"],
                "ItemID" => ["required", "int", "min:1"],
                "Chance" => ["required", "decimal", "min:1"],
                "Quantity" => ["required", "int", "min:1"]
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item)
        {
            $itemObj = DB::Connection()->First("SELECT Name  FROM items WHERE id = :Id", [":Id", $item["ItemID"], \PDO::PARAM_INT]);

            if ($itemObj == null) continue;

            $item["ItemID"] = $item["ItemID"] . " [" . $itemObj["Name"] . "]";
            $item["Chance"] = $item["Chance"] * 100 . "%";
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Exists("SELECT COUNT(*) FROM wheels WHERE ItemID = :ItemID", [":ItemID", $_POST["ItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID " . $_POST["ItemID"] . " is already exists!"]]], 403);
        }

        return $data;
    }

}