<?php

namespace AppBundle\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ItemsController extends ToListItemConvertibleController
{
    const NR_ITEMS = 50;

    /**
     * @param string $itemsType
     * @param int $offset
     * @return Response
     * @throws Exception
     */
    public function indexAction($itemsType, $offset)
    {
        $nrItems = self::NR_ITEMS;
        $offset *= $nrItems;
        switch ($itemsType) {
            case 'authors':
                $authors = $this->get('author')->getAll($nrItems, $offset);
                $items = $this->authorsToListItems($authors);
                break;
            case 'sections':
                $sections = $this->get('section')->getAll($nrItems, $offset);
                $items = $this->sectionsToListItems($sections);
                break;
            case 'books':
                $parallangos = $this
                    ->get('parallango')
                    ->getAll($nrItems, $offset);
                $items = $this->parallangosToListItems($parallangos);
                break;
            default:
                throw new Exception(sprintf(
                    'Can not resolve elements type "%s"', $itemsType
                ));
        }

        return $this->render('@App/list/items.html.twig', [
            'items' => $items,
            'nofollow' => false, // TODO: ???
        ]);
    }
}
