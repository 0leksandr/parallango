<?php

use Utils\PhinxMigration;

class CreateParagraphsTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('paragraphs')
            ->addColumn(
                'parallango_id',
                'integer',
                ['references' => 'parallangos']
            )
            ->addColumn('order', 'integer')
            ->addColumn('position_begin', 'integer')
            ->addColumn('position_end', 'integer')
            ->create();
    }
}
