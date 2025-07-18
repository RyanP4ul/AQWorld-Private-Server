<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class MonstersController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "monsters",
            reqAccess: 40,
            title: "Monsters",
            viewTemplate: "panel/monsters.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "Name" => ["required", "string", "max:32"],
                "File" => ["required", "string", "max:32"],
                "Linkage" => ["required", "string", "max:32"],
                "Level" => ["required", "int"],
                "Health" => ["required", "int"],
                "Mana" => ["required", "int"],
                "Gold" => ["required", "int"],
                "Coin" => ["required", "int"],
                "Experience" => ["required", "int"],
                "ClassPoint" => ["required", "int"],
                "Reputation" => ["required", "int"],
                "DamageReduction" => ["required", "decimal"],
                "DPS" => ["required", "int"],
                "Respawn" => ["required", "int"],
                "Speed" => ["required", "int"],
                "Immune" => ["required", "string", "min:1", "max:2"],
                "WorldBoss" => ["required", "string", "min:1", "max:2"],
            ],
            viewParams: []
        );
    }

}