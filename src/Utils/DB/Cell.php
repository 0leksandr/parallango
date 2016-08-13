<?php

namespace Utils\DB;

use Utils\DB\Exception\DBException;

class Cell
{
    /** @var string|null */
    private $oldValue;
    /** @var mixed */
    private $value;
    /** @var string */
    private $type;
    /** @var string|null */
    private $name;

    private static $groupedSeparators = [
        'INT' => 'intval',
        'TEXT' => 'strval',
    ];

    /**
     * @param string|null $oldValue
     * @param string $type
     * @param string|null $name
     */
    public function __construct($oldValue, $type, $name = null)
    {
        $this->oldValue = $oldValue;
        $this->type = $type;
        $this->name = $name;
        $this->convert();
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @throws DBException
     */
    private function convert()
    {
        if ($this->oldValue === null) {
            $this->value = null;
            return null;
        }
        switch ($this->type) {
            case 'LONGLONG':
            case 'LONG':
                $this->value = (int)$this->oldValue;
                break;
            case 'VAR_STRING':
            case 'STRING':
            case 'BLOB':
//                $this->newValue = (string)$oldValue;
                $this->toStringOrArray();
                break;
            case 'NEWDECIMAL':
                $this->value = floatval($this->oldValue);
                break;
            case 'DOUBLE':
                $this->value = doubleval($this->oldValue);
                break;
            case 'TINY':
                $this->value = (bool)$this->oldValue;
                break;
            default:
                throw new DBException(sprintf('Invalid type: %s', $this->type));
        }
    }

    /**
     * @throws DBException
     */
    private function toStringOrArray()
    {
        if (preg_match('#^(.+)__(\w+)$#', $this->name, $matches)) {
            $name = $matches[1];
            $type = $matches[2];
            if (!isset(self::$groupedSeparators[$type])) {
                throw new DBException('Unknown grouping type: ' . $type);
            }

            $this->name = $name;
            $this->value = array_map(
                self::$groupedSeparators[$type],
                explode($delimiter = '::', $this->oldValue)
            );
        } else {
            $this->value = strval($this->oldValue);
        }
    }
}
