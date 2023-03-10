<#1>
<?php

$ilDB->createTable('uico_uihk_shli_items',
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
