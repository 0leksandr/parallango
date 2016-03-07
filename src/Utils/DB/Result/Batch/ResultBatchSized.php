<?php

namespace Utils\DB\Result\Batch;

class ResultBatchSized extends ResultBatch
{
    /**
     * @param int $batchSize
     * @return array[]|null
     */
    public function fetchBatch($batchSize)
    {
        if ($this->isFinished()) {
            return null;
        }
        $batch = [];
        for ($ii = 0; $ii < $batchSize; $ii++) {
            $row = $this->getResult()->fetch();
            if ($row !== null) {
                $batch[] = $row;
            } else {
                $this->setFinished();
                break;
            }
        }
        return count($batch) === 0 ? null : $batch;
    }
}
