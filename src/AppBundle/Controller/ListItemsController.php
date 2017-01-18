<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ListItemsController extends Controller
{
    /**
     * @param string $entityType
     * @param string|null $relatedEntity
     * @param int|null $relatedId
     * @param int $index
     * @return Response
     */
    public function indexAction(
        $entityType,
        $relatedEntity = null,
        $relatedId = null,
        $index
    ) {
        return $this->render('@App/list/items.html.twig', [
            'items' => $this->get('list_item')->getListItems(
                $entityType,
                $relatedEntity,
                $relatedId,
                $index
            ),
            'nofollow' => false, // TODO: ???
        ]);
    }
}
