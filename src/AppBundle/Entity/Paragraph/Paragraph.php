<?php

namespace AppBundle\Entity\Paragraph;

use AppBundle\Entity\Identifiable;

class Paragraph extends Identifiable
{
    /** @var string */
    private $text;

    /**
     * @param int $id
     * @param string $text
     */
    public function __construct($id, $text)
    {
        parent::__construct($id);
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
