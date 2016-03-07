<?php

namespace Utils\DB\Result\Batch;

use Utils\DB\Result;

abstract class ResultBatch
{
    /** @var Result */
    private $result;
    /** @var array|null */
    private $prevRow = null;
    /** @var bool */
    private $finished = false;

    /**
     * @param Result $result
     */
    public function __construct(Result $result)
    {
        $this->result = $result;
    }

    /**
     * @return Result
     */
    protected function getResult()
    {
        return $this->result;
    }

    /**
     * @return array|null
     */
    protected function getPrevRow()
    {
        return $this->prevRow;
    }

    /**
     * @return bool
     */
    protected function isFinished()
    {
        return $this->finished;
    }

    /**
     * @param array $prevRow
     */
    protected function setPrevRow(array $prevRow)
    {
        $this->prevRow = $prevRow;
    }

    protected function setFinished()
    {
        $this->finished = true;
    }

    /**
     * @param callable $function
     * @return array[]|null
     */
    protected function fetchBatchArrayByFunction(callable $function)
    {
        if ($this->isFinished()) {
            return null;
        }
        $batchArray = [];
        if ($this->getPrevRow() !== null) {
            $batchArray[] = $this->getPrevRow();
        }
        while (true) {
            $curRow = $this->getResult()->fetch();
            if ($curRow === null) {
                $this->setFinished();
                break;
            }
            $this->setPrevRow($curRow);
            $condition = $function($curRow);
            if ($condition) {
                $batchArray[] = $curRow;
            } else {
                break;
            }
        }
        return (count($batchArray) === 0) ? null : $batchArray;
    }
}
