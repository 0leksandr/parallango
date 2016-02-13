<?php

namespace AppBundle\Entity\Language;

use AppBundle\Entity\Identifiable;

class Language extends Identifiable
{
    /** @var string */
    private $code;
    /** @var bool */
    private $isActive;

    /**
     * @param int $id
     * @param string $code
     * @param bool $isActive
     */
    public function __construct($id, $code, $isActive)
    {
        parent::__construct($id);
        $this->code = $code;
        $this->isActive = $isActive;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }
}

