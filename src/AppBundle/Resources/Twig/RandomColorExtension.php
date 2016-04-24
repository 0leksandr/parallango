<?php

namespace AppBundle\Resources\Twig;

class RandomColorExtension extends \Twig_Extension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'random_color';
    }

    /**
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'random_color',
                function () {
                    return $this->getRandomColor();
                }
            ),
        ];
    }

    /**
     * @return string
     */
    public function getRandomColor()
    {
        $color = "#";
        for ($c = 0; $c <3; $c++) {
            $color .= sprintf(
                '%02d',
                base_convert(rand(0 + 2, 255 - 50), 10, 16)
            );
        }
        return $color;
    }
}
