<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author\Author;
use AppBundle\Entity\ItemsList\ItemsList;
use AppBundle\Entity\Language\Language;
use AppBundle\Entity\Parallango\Parallango;
use AppBundle\Entity\Section\Section;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

abstract class PageController extends Controller
{
    /** @var Language[] */
    private $availableLanguages;
    /** @var bool */
    private $isDesktopVersion;

    /** @var string */
    private $languageCode;
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @return string
     */
    abstract protected function getViewName();

    /**
     * @return array
     */
    abstract protected function getParameters();

    /**
     * @return string
     */
    abstract protected function getPageTitle();

    /**
     * @return string[]
     */
    abstract protected function getKeywords();

    /**
     * @return string
     */
    abstract protected function getDescription();

    /**
     * @return string[]|null
     */
    abstract protected function getRobots();

    /**
     * @return array
     */
    abstract protected function getRequestParams();

    /**
     * @return string[]
     */
    abstract protected function getJavaScripts();

    /**
     * @param string $languageCode
     * @param bool $isDesktopVersion
     */
    public function __construct(
        $languageCode = Language::EN,
        $isDesktopVersion = false
    ) {
        $this->languageCode = $languageCode;
        $this->isDesktopVersion = $isDesktopVersion;
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     * @throws Exception
     */
    public final function indexAction(Request $request)
    {
        $this->initialize($request);
        $method = $request->getMethod();
        switch ($method) {
            case Request::METHOD_GET:
                return $this->getIndexResponse();
            case Request::METHOD_POST:
                return $this->getAjaxResponse();
            default:
                throw new Exception(sprintf('Unsupported method: %s', $method));
        }
    }

    /**
     * @return string|null
     */
    protected function getPageClass()
    {
        return null;
    }

    /**
     * @return string[]
     */
    protected function getStylesheets()
    {
        return [];
    }

    /**
     * @param Request $request
     */
    protected function initialize(Request $request)
    {
        $this->translator = $this->get('translator');

        $language = $this->get('language')->get($request->getLocale());
        $this->container->set('language.current', $language);
    }

    /**
     * @return TranslatorInterface
     */
    protected final function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return Language
     */
    protected final function getLanguage()
    {
        return $this->get('language')->get($this->languageCode);
    }

    /**
     * @param string $entityType
     * @param string|null $relatedEntity
     * @param int|null $relatedId
     * @return ItemsList
     */
    protected function getItemsList(
        $entityType,
        $relatedEntity = null,
        $relatedId = null
    ) {
        return new ItemsList(
            false,
            $this->getListHeader($entityType),
            $this->get('list_item')->getListItems(
                $entityType,
                $relatedEntity,
                $relatedId,
                0
            ),
            $this->getListUploadUrlPrefix(
                $entityType,
                $relatedEntity,
                $relatedId
            ),
            false
        );
    }

    /**
     * @return array
     */
    private function getAllParameters()
    {
        return array_merge(
            $this->getParameters(),
            $this->getTitleParameters(),
            [
                'view' => $this->getViewName(),
                'is_desktop_version' => $this->isDesktopVersion,
                'language' => $this->getLanguage(),
                'available_languages' => $this->getAvailableLanguages(),
                'keywords' => implode(
                    ', ',
                    array_map('trim', $this->getKeywords())
                ),
                'description' => $this->getDescription(),
                'robots' => $this->getRobots(),
                'stylesheets' => ['style.css'] + $this->getStylesheets(),
                'scripts' => $this->getJavaScripts(),
                'page_class' => $this->getPageClass(),
                'request_params' => $this->getRequestParams(),
            ]
        );
    }

    /**
     * @return array
     */
    private function getTitleParameters()
    {
        return ['title' => $this->getPageTitle()];
    }

    /**
     * @return Language[]
     */
    private function getAvailableLanguages()
    {
        if ($this->availableLanguages === null) {
            $this->availableLanguages = $this->get('language')->getActive();
        }
        return $this->availableLanguages;
    }

    /**
     * @return Response
     */
    private function getIndexResponse()
    {
        $response = $this->render(
            $this->getHtmlFileLocation('page'),
            $this->getAllParameters()
        );
        $response->headers->add([
            'Content-Type' => 'text/html',
            'charset' => 'utf-8',
        ]);

        return $response;
    }

    /**
     * @return JsonResponse
     */
    private function getAjaxResponse()
    {
        $content = $this
            ->render(
                $this->getHtmlFileLocation($this->getViewName()),
                $this->getAllParameters()
            )
            ->getContent();

        return new JsonResponse(array_merge(
            ['content' => $content],
            $this->getTitleParameters()
        ));
    }

    /**
     * @param string $viewName
     * @return string
     */
    private function getHtmlFileLocation($viewName)
    {
        return sprintf('@App/%s.html.twig', $viewName); // 'AppBundle::%s.html.twig'
    }

    /**
     * @param string $entityType
     * @return string
     * @throws Exception
     */
    private function getListHeader($entityType)
    {
        $translator = $this->get('translator');
        switch ($entityType) {
            case Author::ENTITY_TYPE:
                return $translator->trans('items-header-authors');
            case Section::ENTITY_TYPE:
                return $translator->trans('items-header-sections');
            case Parallango::ENTITY_TYPE:
                return $translator->trans('items-header-books');
        }
        throw new Exception(sprintf(
            'Unknown entity type: %s',
            $entityType
        ));
    }

    /**
     * @param string $entityType
     * @param string|null $relatedEntity
     * @param int|null $relatedId
     * @return string
     */
    private function getListUploadUrlPrefix(
        $entityType,
        $relatedEntity = null,
        $relatedId = null
    ) {
        $router = $this->get('router');
        $dummyIndex = 0;
        if ($relatedEntity === null) {
            $uploadUrl = $router->generate('items', [
                'entityType' => $entityType,
                'index' => $dummyIndex,
            ]);
        } else {
            $uploadUrl = $router->generate('items_related', [
                'entityType' => $entityType,
                'relatedEntity' => $relatedEntity,
                'relatedId' => $relatedId,
                'index' => $dummyIndex,
            ]);
        }
        return _preg_match(
            sprintf('#^(.*/)%d/?$#', $dummyIndex),
            $uploadUrl
        )[1];
    }
}
