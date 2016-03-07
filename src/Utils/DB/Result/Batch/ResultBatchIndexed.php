<?php

namespace Utils\DB\Result\Batch;

use Utils\DB\Result;

class ResultBatchIndexed extends ResultBatch
{
    /** @var string */
    private $indexColumn;
    /** @var mixed|null */
    private $prevBatchIndexValue = null;

    /**
     * @param Result $result
     * @param string $indexColumn
     */
    public function __construct(Result $result, $indexColumn)
    {
        parent::__construct($result);
        $this->indexColumn = $indexColumn;
    }

    /**
     * @return array[]|null
     */
    public function fetchBatchArray()
    {
        $function = function ($row) {
            $res =
                $this->prevBatchIndexValue === null
                || $row[$this->indexColumn] === $this->prevBatchIndexValue;
            $this->prevBatchIndexValue =
                $this->getPrevRow()[$this->indexColumn];
            return $res;
        };
        $batchArray = $this->fetchBatchArrayByFunction($function);
        if (!$this->isFinished()) {
            $this->prevBatchIndexValue =
                $this->getPrevRow()[$this->indexColumn];
        }
        return $batchArray;
    }
}
