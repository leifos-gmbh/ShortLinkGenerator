<#1>
<?php
/**
* This file is part of ILIAS, a powerful learning management system
* published by ILIAS open source e-Learning e.V.
*
* ILIAS is licensed with the GPL-3.0,
* see https://www.gnu.org/licenses/gpl-3.0.en.html
* You should have received a copy of said license along with the
* source code, too.
*
* If this is not the case or you just want to try ILIAS, you'll find
* us at:
* https://www.ilias.de
* https://github.com/ILIAS-eLearning
*
*********************************************************************/

$ilDB->createTable(
    'uico_uihk_shli_items',
    [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'title' => [
            'type' => 'text',
            'length' => 40,
            'notnull' => true
        ],
        'url' =>
        [
            'type' => 'text',
            'length' => 300,
            'notnull' => true,
        ],
        'last_update' =>
        [
            'type' => 'timestamp',
            'notnull' => false
        ]
    ]
);

$ilDB->addPrimaryKey('uico_uihk_shli_items', ['id']);
$ilDB->createSequence('uico_uihk_shli_items');
?>
