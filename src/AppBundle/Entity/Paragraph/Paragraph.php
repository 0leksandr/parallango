<?php

namespace AppBundle\Entity\Paragraph;

use AppBundle\Entity\Identifiable;

class Paragraph extends Identifiable
{
    /** @var string */
    private $textLeft;
    /** @var string */
    private $textRight;

    /**
     * @param int $id
     * @param string $textLeft
     * @param string $textRight
     */
    public function __construct($id, $textLeft, $textRight)
    {
        parent::__construct($id);
        $this->textLeft = $textLeft;
        $this->textRight = $textRight;
    }

    /**
     * @return string
     */
    public function getTextLeft()
    {
        return $this->textLeft;
    }

    /**
     * @return string
     */
    public function getTextRight()
    {
        return $this->textRight;
    }
}
