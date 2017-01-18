<?php

namespace Base\Commands\Seed;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\Parallango\Parallango;
use AppBundle\Entity\Section\Section;
use Utils\DB\ValuesList;

class EntityTypes extends AbstractSeedCommand
{
    /**
     * @return string[]
     */
    protected function getTableNames()
    {
        return ['entity_types'];
    }

    protected function seed()
    {
        $entityTypes = [
            Author::ENTITY_TYPE,
            Section::ENTITY_TYPE,
            Parallango::ENTITY_TYPE,
        ];
        $this->sql()->execute(
            <<<'SQL'
            INSERT INTO entity_types(entity_type)
            VALUES :entity_types
SQL
            ,
            [
                'entity_types' => new ValuesList(
                    array_map(
                        function ($entityType) {
                            return [$entityType];
                        },
                        $entityTypes
                    )
                ),
            ]
        );
    }
}
