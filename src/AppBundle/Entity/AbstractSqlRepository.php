<?php

namespace AppBundle\Entity;

use Exception;
use Utils\DB\Literal;
use Utils\DB\SQL;

require_once __DIR__ . '/../../Utils/Utils.php';

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
     */
    public function getByIds(array $ids)
    {
        $data = $this->sql->getArray(
            $this->getDataByIdsQuery(),
            $this->extendParams(['ids' => $ids])
        );
        if ($data === null) {
            $this->throwEx(sprintf(
                'Can not get rows for ids: %s',
                implode(', ', $ids)
            ));
        }
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
     * @param array $params
     * @return Identifiable[]
     */
    protected function getBySelectIdsQuery($query, array $params = [])
    {
        $data = $this->sql->getArray(
            $this->getDataByIdsQuery(),
            array_merge(
                ['ids' => new Literal(
                    <<<TEXT
(
    {$query}
)
TEXT
                )],
                $this->extendParams($params)
            )
        );

        return $this->getByData($data);
    }

    /**
     * @param $query
     * @param array $params
     * @return Identifiable
     */
    protected function getSingleBySelectIdQuery($query, array $params = [])
    {
        $entities = $this->getBySelectIdsQuery($query, $params);
        if (count($entities) !== 1) {
            $this->throwEx(sprintf(
                'Expected 1 entity, got %d',
                count($entities)
            ));
        }

        return head($entities);
    }

    /**
     * @param array $array
     * @return array
     */
    protected function getRowFromArray(array $array)
    {
        if (($nrRows = count($array)) !== 1) {
            $this->throwEx(sprintf(
                'Bad number of rows: %d',
                $nrRows
            ));
        }

        $row = head($array);
        if (!is_array($row)) {
            $this->throwEx('Row is not an array');
        }

        return $row;
    }

    /**
     * @param string $message
     * @throws Exception
     */
    protected function throwEx($message)
    {
        throw new Exception(sprintf('%s: %s', get_class($this), $message));
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
     */
    private function checkIds(array $ids, array $entities)
    {
        if ($missingIds = array_diff($ids, mpull($entities, 'getId'))) {
            $this->throwEx(sprintf(
                'Can not find entities with ids: %s',
                implode(', ', $missingIds)
            ));
        }
    }

    /**
     * @param array $params
     * @return array
     */
    private function extendParams(array $params)
    {
        return $params + [
            'LIMIT' => null,
            'offset' => 0,
        ];
    }
}
