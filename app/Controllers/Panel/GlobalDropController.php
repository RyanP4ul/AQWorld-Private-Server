<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class GlobalDropController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "global_drops",
            reqAccess: 40,
            title: "Global Drops",
            viewTemplate: "panel/globaldrop.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "ItemID" => ["required", "int"],
                "Chance" => ["required", "decimal"],
                "Quantity" => ["required", "int"],
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item)
        {
            $itemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :id", [":id", $item["ItemID"], PDO::PARAM_INT]);

            $item["ItemID"] = $itemObj == null ? $item["ItemID"] : "[" . $item["ItemID"] . "] " . $itemObj["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

}