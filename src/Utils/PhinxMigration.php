<?php

namespace Utils;

use Phinx\Db\Table\ForeignKey;
use Phinx\Migration\AbstractMigration;

require_once 'Utils.php';

abstract class PhinxMigration extends AbstractMigration
{
    /** @var Table[] */
    private $tables = [];

    abstract public function change();

    /**
     * @param string $name
     * @param array $options
     * @return Table
     */
    public function table($name, $options = [])
    {
        if (!isset($options['engine'])) {
            $options['engine'] = 'MyISAM';
        }

        $table = new Table($name, $options);
        $table->setAdapter($this->getAdapter());
        $this->tables[] = $table;
        return $table;
    }
}

class Table extends \Phinx\Db\Table
{
    /**
     * @param \Phinx\Db\Table\Column|string $name
     * @param string|null $type
     * @param array $options
     * @return \Phinx\Db\Table
     */
    public function addColumn($name, $type = null, $options = [])
    {
        if (isset($options['references'])) {
            $references = $options['references'];
            if (is_string($references)) {
                $referencedTable = $references;
                $referencedColumn = ['id'];
            } else {
                list($referencedTable, $referencedColumn) = $references;
            }
            $referenceOptions = [
                'update' => ForeignKey::RESTRICT,
                'delete' => ForeignKey::RESTRICT,
//                'constraint',
            ];
            $this->addForeignKey(
                $name,
                $referencedTable,
                $referencedColumn,
                $referenceOptions
            );
            unset($options['references']);
        }
        if (!isset($options['null'])) {
            $options['null'] = false;
        }

        return parent::addColumn($name, $type, $options);
    }

//    public function __destruct()
//    {
//        $adapter = $this->getAdapter();
//        if (!$adapter->hasTable($this->getName())) {
//echo(__FILE__.":".__LINE__.PHP_EOL);
//            $this->create();
//        } else {
//            $existingColumns = mpull(
//                $adapter->getColumns($this->getName()),
//                'getName'
//            );
//
//            // TODO: make better id procession
//            if (in_array('id', $existingColumns)) {
//                foreach (array_keys($existingColumns, 'id') as $key) {
//                    unset($existingColumns[$key]);
//                }
//                $existingColumns = array_values($existingColumns);
//            }
//echo(__FILE__.":".__LINE__." ".print_r($existingColumns,true).PHP_EOL);
//            $pendingColumns = mpull($this->getPendingColumns(), 'getName');
//echo(__FILE__.":".__LINE__." ".print_r($pendingColumns,true).PHP_EOL);
//            sort($existingColumns);
//            sort($pendingColumns);
//            if ($existingColumns === $pendingColumns) {
//echo(__FILE__.":".__LINE__.PHP_EOL);
//                $this->drop();
//                if ($adapter instanceof ProxyAdapter) {
//                    $adapter->executeCommands();
//                }
//            } else {
//echo(__FILE__.":".__LINE__.PHP_EOL);
//                $this->update();
//            }
//        }
//    }
}
