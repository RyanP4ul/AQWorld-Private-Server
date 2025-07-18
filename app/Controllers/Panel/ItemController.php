<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class ItemController extends BasePanelController
{

    public const ITEM_TYPES = ["Item", "Armor", "Class", "Axes", "Bows", "Capes", "Daggers", "Gauntlet", "Grounds", "Guns", "Hair", "Helms", "House", "Floor Item", "Maces", "Pets", "Polearms", "Staff", "Staves", "Sword", "ServerUse", "Potion"];
    public const ITEM_EQUIPMENTS = ["Weapon", "co", "ar", "ba", "he", "pe", "am", "hi", "ho"];

    public function __construct()
    {
        parent::__construct(
            table: "items",
            reqAccess: 40,
            title: "Items",
            viewTemplate: "panel/items.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "Name" => ["required", "string", "max:32"],
                "Type" => ["required", "string"],
                "Icon" => ["required", "string"],
                "Equipment" => ["required", "string"],

                "Description" => ["required", "string", "max:255"],
                "File" => ["string", "max:255"],
                "Link" => ["string", "max:255"],
                "Level" => ["required", "int"],
                "DPS" => ["required", "int"],

                "Range" => ["required", "int"],
                "Rarity" => ["required", "int"],
                "Quantity" => ["required", "int"],
                "Stack" => ["required", "int"],
                "Cost" => ["required", "int"],

                "EnhID" => ["required", "int"],
                "FactionID" => ["required", "int"],
                "ReqReputation" => ["int"],
                "ReqClassID" => ["int"],
                "ReqClassPoints" => ["int"],
                "ReqQuests" => [ "string"],
                "Meta" => ["string", "max:50"],
                "QuestStringIndex" => ["int"],
                "QuestStringValue" => ["int"],

                "Sell" => ["string"],
                "Temporary" => ["string"],
                "Upgrade" => ["string"],
                "Staff" => ["string"],
            ],
            viewParams: [
                "types" => self::ITEM_TYPES,
                "equipments" => self::ITEM_EQUIPMENTS,
            ]
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item)
        {
            $tags = "";

            if ($item["Temporary"]) $tags .= " <span class='badge bg-success'>Temporary</span>";
            if ($item["Upgrade"]) $tags .= " <span class='badge bg-danger'>Upgrade</span>";
            if ($item["Staff"]) $tags .= " <span class='badge bg-warning text-white'>Staff</span>";

//                $item["Image"] = "<img src='https://placehold.co/300' class='rounded' style='width:90px' alt='image-" . $item["Name"] . "'>";
            $item["Tags"] = empty($tags) ? "None" : $tags;
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }


    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("items", "Name", $_POST["Name"]))
        {
            $this->response_json(["msg" => ["Name" => [$_POST["Name"] . " Name has already been taken."]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM enhancements WHERE id = :Id", [":Id", $_POST["EnhID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["EnhID" => ["EnhID does not exists!"]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM factions WHERE id = :Id", [":Id", $_POST["FactionID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["FactionID" => ["FactionID does not exists!"]]], 403);
        }

        return $data;
    }

}