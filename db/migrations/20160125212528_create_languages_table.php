<?php

use Utils\PhinxMigration;

class CreateLanguagesTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('languages')
            ->addColumn('code', 'string', ['length' => 2])
            ->addColumn('is_active', 'boolean')
            ->create();
    }
}
