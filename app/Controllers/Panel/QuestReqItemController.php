<?php

namespace App\Controllers\Panel;

use Database\DB;
use PDO;

class QuestReqItemController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "quests_required_items",
            reqAccess: 40,
            title: "Quests Req Items",
            viewTemplate: "panel/quests.reqitem.html.twig",
            validationRules: [
                "id" => ["required", "int", "min:1"],
                "QuestID" => ["required", "int"],
                "ItemID" => ["required", "int"],
                "Quantity" => ["required", "int", "min:0", "max:999"],
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $questObj = DB::Connection()->First("SELECT Name FROM quests WHERE id = :Id", [":Id", $item["QuestID"], PDO::PARAM_INT]);
            $itemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :Id", [":Id", $item["ItemID"], PDO::PARAM_INT]);

            $item["QuestID"] = $questObj == null ? $item["QuestID"] : "[" . $item["QuestID"] . "] " . $questObj["Name"];
            $item["ItemID"] = $itemObj == null ? $item["ItemID"] : "[" . $item["ItemID"] . "] " . $itemObj["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }


    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Count("SELECT COUNT(*) FROM quests WHERE id = :Id", [":Id", $_POST["QuestID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["QuestID" => ["QuestID does not exists!"]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM items WHERE id = :Id", [":Id", $_POST["ItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID does not exists!"]]], 403);
        }

        if ($_GET["type"] != "update" && DB::Connection()->Count("SELECT COUNT(*) FROM quests_required_items WHERE QuestID = :QuestID AND ItemID = :ItemID", [
                [":QuestID", $_POST["QuestID"], PDO::PARAM_INT],
                [":ItemID", $_POST["ItemID"], PDO::PARAM_INT]
            ]) > 0)
        {
            $this->response_json(["msg" => ["QuestID" => ["QuestID and ItemID are already exists."]]], 403);
        }

        return $data;
    }

}