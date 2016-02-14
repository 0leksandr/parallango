<?php

namespace AppBundle\Entity;

use Exception;
use Utils\DB\Literal;
use Utils\DB\SQL;

abstract class AbstractSqlRepository extends AbstractRepository
{
    /** @var SQL */
    protected $sql;

    /**
     * @param SQL $sql
     */
    public function __construct(SQL $sql)
    {
        $this->sql = $sql;
    }

    /**
     * @return string should have ":ids" inside
     */
    abstract protected function getDataByIdsQuery();

    /**
     * @param int[] $ids
     * @return Identifiable[]
     * @throws Exception
     */
    public function getByIds(array $ids)
    {
        $data = $this->sql->getArray(
            $this->getDataByIdsQuery(),
            ['ids' => $ids]
        );
        $entities = $this->getByData($data);

        $this->checkIds($ids, $entities);

        return $entities;
    }

    /**
     * @param int $id
     * @return Identifiable
     */
    public function getById($id)
    {
        return head($this->getByIds([$id]));
    }

    /**
     * @param string $query
     * @return Identifiable[]
     */
    protected function getBySelectIdsQuery($query)
    {
        $data = $this->sql->getArray(
            $this->getDataByIdsQuery(),
            ['ids' => new Literal($query)]
        );

        return $this->getByData($data);
    }

    /**
     * @param array $array
     * @return array
     * @throws Exception
     */
    protected function getRowFromArray(array $array)
    {
        if (($nrRows = count($array)) !== 1) {
            throw new Exception(sprintf(
                'Bad number of rows for %s: %d',
                get_class($this),
                $nrRows
            ));
        }

        $row = head($array);
        if (!is_array($row)) {
            throw new Exception(sprintf(
                'Row is not an array in %s',
                get_class($this)
            ));
        }

        return $row;
    }

    /**
     * @param array $data
     * @return Identifiable[]
     */
    private function getByData(array $data)
    {
        $entities = [];
        foreach (igroup($data, 'id') as $id => $data) {
            $entities[] = $this->getByIdData($id, $data);
        }

        return $entities;
    }

    /**
     * @param int[] $ids
     * @param Identifiable[] $entities
     * @throws Exception
     */
    private function checkIds(array $ids, array $entities)
    {
        if ($missingIds = array_diff($ids, mpull($entities, 'getId'))) {
            throw new Exception(
                '%s can not find entities with ids: %s',
                get_class($this),
                implode(', ', $missingIds)
            );
        }
    }
}
