<?php

namespace App\Controllers\Panel;

class SkillController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "skills",
            reqAccess: 60,
            title: "Skills",
            viewTemplate: "panel/skills.html.twig",
            validationRules: [
                "id" => ["required", "int", "min:1"],
                "Name" => ["required", "string", "min:1", "max:20"],
                "Animation" => ["required", "string"],
                "Description" => ["required", "string", "max:100"],
                "Damage" => ["required", "decimal"],
                "Mana" => ["required", "int"],
                "Icon" => ["required", "string", "max:30"],
                "Range" => ["required", "int", "min:100", "max:3000"],
                "Reference" => ["required", "string", "min:2", "max:2"],
                "Target" => ["required", "string"],
                "Effects" => ["required", "string"],
                "Type" => ["required", "string"],
                "Strl" => ["required", "string"],
                "Cooldown" => ["required", "int"],
                "HitTargets" => ["required", "int"],
            ],
            viewParams: []
        );
    }

}