<?php

namespace AppBundle\Entity\Paragraph;

use AppBundle\Entity\Identifiable;

class Paragraph extends Identifiable
{
    /** @var string */
    private $text;

    /**
     * @param int $id
     * @param string $textLeft
     */
    public function __construct($id, $textLeft)
    {
        parent::__construct($id);
        $this->text = $textLeft;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
