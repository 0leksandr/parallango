<?php

use Utils\PhinxMigration;

class CreateMaterializedPagesTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('page_sizes')
            ->addColumn('page_size_symbols', 'integer')
            ->create();

        $this
            ->table('materialized_pages')
            ->addColumn(
                'parallango_id',
                'integer',
                ['references' => 'parallangos']
            )
            ->addColumn(
                'page_size_id',
                'integer',
                ['references' => 'page_sizes']
            )
            ->addColumn(
                'paragraph_id',
                'integer',
                ['references' => 'paragraphs']
            )
            ->create();
    }
}
