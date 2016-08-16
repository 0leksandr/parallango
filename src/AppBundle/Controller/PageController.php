<?php

namespace AppBundle\Controller;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Language\Language;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

abstract class PageController extends SymfonyController
{
    /** @var Language[] */
    private $availableLanguages;
    /** @var bool */
    private $isDesktopVersion;
    /** @var Request */
    private $request;

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
     * @return Response
     */
    public final function indexAction(Request $request)
    {
        $this->request = $request;
        $this->initialize();
        $response = $this->render(
            'AppBundle::page.html.twig',
            $this->getAllParameters()
        );
        $response->headers->add([
            'Content-Type' => 'text/html',
            'charset' => 'utf-8',
        ]);

        return $response;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
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

    protected function initialize()
    {
        $this->translator = $this->get('translator');
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @return Language
     */
    protected function getLanguage()
    {
        return $this->get('language')->get($this->languageCode);
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
     * @return array
     */
    private function getAllParameters()
    {
        return array_merge($this->getParameters(), [
            'view' => $this->getViewName(),
            'is_desktop_version' => $this->isDesktopVersion,
            'language' => $this->getLanguage(),
            'available_languages' => $this->getAvailableLanguages(),
            'title' => $this->getPageTitle(),
            'keywords' => implode(
                ', ',
                array_map('trim', $this->getKeywords())
            ),
            'description' => $this->getDescription(),
            'robots' => $this->getRobots(),
            'stylesheets' => ['style.css'] + $this->getStylesheets(),
            'path_static' => 'http://localhost:8000',
            'page_class' => $this->getPageClass(),
            'request_params' => $this->getRequestParams(),
        ]);
    }
}
