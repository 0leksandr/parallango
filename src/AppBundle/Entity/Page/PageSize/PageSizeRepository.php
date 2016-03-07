<?php

namespace AppBundle\Entity\Page\PageSize;

use AppBundle\Entity\AbstractSqlRepository;

/**
 * @method PageSize getById($id)
 */
class PageSizeRepository extends AbstractSqlRepository
{
    /**
     * @return PageSize[]
     */
    public function getAll()
    {
        return $this->getBySelectIdsQuery(
            <<<'SQL'
            SELECT id
            FROM page_sizes
SQL
        );
    }

    /**
     * @param array $data
     * @return PageSize
     */
    protected function createByData(array $data)
    {
        $this->mandatory($data, ['id', 'page_size_symbols']);
        $row = $this->getRowFromArray($data);
        return new PageSize($row['id'], $row['page_size_symbols']);
    }

    /**
     * @return string
     */
    protected function getDataByIdsQuery()
    {
        return <<<'SQL'
            SELECT
                id,
                page_size_symbols
            FROM page_sizes
            WHERE id IN :ids
SQL;
    }
}
