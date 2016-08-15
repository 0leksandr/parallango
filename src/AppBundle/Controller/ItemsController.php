<?php

namespace AppBundle\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ItemsController extends Controller
{
    /**
     * @param string $languageCode
     * @param string $itemsType
     * @param int $offset
     * @return Response
     * @throws Exception
     */
    public function indexAction($languageCode, $itemsType, $offset)
    {
        $nrItems = 50;
        $offset *= $nrItems;
        switch ($itemsType) {
            case 'author':
                $items = $this->get('author')->getAll($nrItems, $offset);
                break;
            case 'section':
                $items = $this->get('section')->getAll($nrItems, $offset);
                break;
            case 'parallango':
                $items = $this->get('parallango')->getAll($nrItems, $offset);
                break;
            default:
                throw new Exception(sprintf(
                    'Can not resolve elements type "%s"', $itemsType
                ));
        }

        return $this->render('@App/list/items.html.twig', [
            'items' => $items,
            'type' => $itemsType, // TODO: detect automatically based on items
            'language' => $this->get('language')->get($languageCode),
            'nofollow' => true, // TODO: ???
        ]);
    }
}
