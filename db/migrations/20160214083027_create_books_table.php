<?php

use Utils\PhinxMigration;

class CreateBooksTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('books')
            ->addColumn('author_id', 'integer', ['references' => 'authors'])
            ->addColumn('language_id', 'integer', ['references' => 'languages'])
            ->addColumn(
                'section_id',
                'integer',
                ['references' => 'sections', 'null' => true]
            )
            ->addColumn('title', 'string', ['length' => 255])
            ->create();
    }
}
