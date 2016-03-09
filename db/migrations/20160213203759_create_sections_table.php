<?php

use Utils\PhinxMigration;

class CreateSectionsTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('sections')
            ->create();
        $this
            ->table('section_titles')
            ->addColumn('section_id', 'integer', ['references' => 'sections'])
            ->addColumn('language_id', 'integer', ['references' => 'languages'])
            ->addColumn('title', 'string', ['length' => 64])
            ->create();
    }
}
