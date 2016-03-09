<?php

use Utils\PhinxMigration;

class CreateParallangosTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('parallangos')
            ->addColumn('left_book_id', 'integer', ['references' => 'books'])
            ->addColumn('right_book_id', 'integer', ['references' => 'books'])
            ->create();
    }
}
