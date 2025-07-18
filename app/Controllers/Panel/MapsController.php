<?php

namespace App\Controllers\Panel;

use App\config;
use App\Controllers\Controller;
use Database\DB;
use Exception;
use PDO;
use PDOException;

class MapsController extends BasePanelController
{

    private const FILTER_COLUMNS = ['staff' => 'Staff', 'worldboss' => 'WorldBoss'];

    public function __construct()
    {
        parent::__construct(
            table: "maps",
            reqAccess: 40,
            title: "Maps",
            viewTemplate: "panel/maps.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "Name" => ["required", "string", "max:32"]
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        $type = isset($_GET["type"]) ? filter_input(INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : "None";

        if (isset(self::FILTER_COLUMNS[$type])) {
            $column = self::FILTER_COLUMNS[$type];
            $items = DB::Connection()->Get("SELECT * FROM maps WHERE {$column} = 1");
        } else {
            $items = DB::Connection()->Get("SELECT * FROM maps");
        }

        foreach ($items as &$item) {
//                $map["Image"] = "<img src='https://placehold.co/400' class='rounded' style='width:90px' alt='image-" . $map["Name"] . "'>";

            $tags = '';

            if ($item["ReqParty"]) $tags .= " <span class='badge bg-primary'>Party</span>";
            if ($item["Upgrade"]) $tags .= " <span class='badge bg-secondary'>Upgrade</span>";
            if ($item["Staff"]) $tags .= " <span class='badge bg-success'>Staff</span>";
            if ($item["PvP"]) $tags .= " <span class='badge bg-danger'>PvP</span>";
            if ($item["WorldBoss"]) $tags .= " <span class='badge bg-warning text-white'>WorldBoss</span>";

            $item["Tags"] = empty($tags) ? "None" : $tags;
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-trash pr-2'></i> Delete</button>";

        }
    }

}