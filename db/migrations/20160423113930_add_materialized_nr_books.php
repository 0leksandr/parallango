<?php

use Utils\PhinxMigration;

class AddMaterializedNrBooks extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('mat_nr_books_authors')
            ->addColumn('author_id', 'integer', ['references' => 'authors'])
            ->addColumn('language1_id', 'integer', [
                'references' => 'languages',
            ])
            ->addColumn('language2_id', 'integer', [
                'references' => 'languages',
            ])
            ->addColumn('nr_books', 'integer')
            ->create();

        $this
            ->table('mat_nr_books_sections')
            ->addColumn('section_id', 'integer', ['references' => 'sections'])
            ->addColumn('language1_id', 'integer', [
                'references' => 'languages',
            ])
            ->addColumn('language2_id', 'integer', [
                'references' => 'languages',
            ])
            ->addColumn('nr_books', 'integer')
            ->create();
    }
}
