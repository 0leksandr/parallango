<?php

namespace AppBundle\Controller;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Language\Language;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class PageController extends SymfonyController
{
    /** @var Language[] */
    private $availableLanguages;
    /** @var string */
    private $languageCode;
    /** @var bool */
    private $isDesktopVersion;

    /**
     * @return string
     */
    abstract public function getViewName();

    /**
     * @param Request $request
     * @return array|null
     */
    abstract public function getParameters(Request $request);

    /**
     * @return string
     */
    abstract public function getPageTitle();

    /**
     * @return string[]
     */
    abstract public function getKeywords();

    /**
     * @return string
     */
    abstract public function getDescription();

    /**
     * @return string[]|null
     */
    abstract public function getRobots();

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
     * @return string|null
     */
    public function getPageClass()
    {
        return null;
    }

    /**
     * @return string[]
     */
    public function getStylesheets()
    {
        return [];
    }

    public function initialize()
    {
    }

    /**
     * @param Request $request
     * @return Response
     */
    public final function indexAction(Request $request)
    {
        $response = $this->render(
            'AppBundle::page.html.twig',
            $this->getAllParameters($request)
        );
        $response->headers->add([
            'Content-Type' => 'text/html',
            'charset' => 'utf-8',
        ]);

        return $response;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public final function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->initialize();
    }

    /**
     * @return Language
     */
    private function getLanguage()
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
     * @param Request $request
     * @return array
     */
    private function getAllParameters(Request $request)
    {
        return array_merge($this->getParameters($request) ?: [], [
            'view' => $this->getViewName(),
            'is_desktop_version' => $this->isDesktopVersion,
            'language' => $this->getLanguage(),
            'available_languages' => $this->getAvailableLanguages(),
            'title' => $this->getPageTitle(),
            'keywords' => implode(', ', $this->getKeywords()),
            'description' => $this->getDescription(),
            'robots' => $this->getRobots(),
            'stylesheets' => ['style.css'] + $this->getStylesheets(),
            'path_static' => 'http://localhost:8000',
            'page_class' => $this->getPageClass(),
        ]);
    }
}
