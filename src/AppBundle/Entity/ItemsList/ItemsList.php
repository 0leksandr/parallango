<?php

namespace AppBundle\Entity\ItemsList;

class ItemsList
{
    /** @var bool */
    private $active;
    /** @var string */
    private $header;
    /** @var ListItem[] */
    private $items;
    /** @var string */
    private $uploadUrlPrefix;
    /** @var bool */
    private $nofollow; // TODO: follow?

    /**
     * @param bool $active
     * @param string $header
     * @param ListItem[] $items
     * @param string $uploadUrlPrefix
     * @param bool $nofollow
     */
    public function __construct(
        $active,
        $header,
        array $items,
        $uploadUrlPrefix,
        $nofollow
    ) {
        $this->active = $active;
        $this->header = $header;
        $this->items = $items;
        $this->uploadUrlPrefix = $uploadUrlPrefix;
        $this->nofollow = $nofollow;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return ListItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function getUploadUrlPrefix()
    {
        return $this->uploadUrlPrefix;
    }

    /**
     * @return bool
     */
    public function isNofollow()
    {
        return $this->nofollow;
    }
}
