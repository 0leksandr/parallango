<?php

use Utils\PhinxMigration;

class CreateAuthorsTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('authors')
            ->create();
        $this
            ->table('author_language_property')
            ->addColumn('property_name', 'string', ['length' => 32])
            ->create();
        $this
            ->table('author_language_properties')
            ->addColumn('author_id', 'integer', ['references' => 'authors'])
            ->addColumn('language_id', 'integer', ['references' => 'languages'])
            ->addColumn(
                'property_id',
                'integer',
                ['references' => 'author_language_property']
            )
            ->addColumn('property_value', 'string', ['length' => 128])
            ->create();
    }
}
