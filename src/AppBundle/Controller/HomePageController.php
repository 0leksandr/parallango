<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Language\Language;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class HomePageController extends PageController
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @return string
     */
    public function getViewName()
    {
        return 'home';
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public function getParameters(Request $request)
    {
        return
            $this->getPreview()
            + $this->getMobilePreview()
            + [
                'authors' => $this
                    ->get('author')
                    ->getAll(),
            ] + [
                'sections' => $this
                    ->get('section')
                    ->getAll(),
            ] + [
                'parallangos' => $this
                    ->get('parallango')
                    ->getAll(),
            ];
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->translator->trans('default-page-title');
    }

    /**
     * @return string[]
     */
    public function getKeywords()
    {
//        return [
//            'Learn English',
//            'read books in original',
//            'parallel translation',
//            'read books in English',
//        ];
        return [$this->translator->trans('default-page-keywords')];
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->translator->trans('default-page-description');
    }

    /**
     * @return string[]|null
     */
    public function getRobots()
    {
        return null;
    }

    public function initialize()
    {
        $this->translator = $this->get('translator');
    }

    /**
     * @return string
     */
    public function getPageClass()
    {
        return 'page_index';
    }

    /**
     * @return array
     */
    private function getPreview()
    {
        do {
            $randomBook = $this->get('parallango')->getRandom();
            $pageSize = $this->get('page_size')->get(5000);
            $pages = $this->get('page')->getByParallangoAndPageSize(
                $randomBook,
                $pageSize
            );
            $randomPage = $pages[rand(2, count($pages) - 2)];
        } while (
(//TODO:fix
            strlen($randomPage->getText()) !== $pageSize->getPageSizeSymbols()
)&&false
        );

        return [
            'book' => $randomBook,
            'random_page' => $randomPage,
        ];
    }

    /**
     * @return array
     */
    private function getMobilePreview()
    {
        // TODO: make it normal
        /**
         * @var TranslatorInterface $translator1
         * @var TranslatorInterface $translator2
         */
        list($translator1, $translator2) = $this->getTranslators();
        $left = explode('|', $translator1->trans('mobile-preview'));
        $right = explode('|', $translator2->trans('mobile-preview'));
        $nrRows = max([count($left), count($right)]);
        if ($nrRows > count($left)) {
            $left += array_fill(count($left), $nrRows - count($left), '');
        }
        if ($nrRows > count($right)) {
            $right += array_fill(count($right), $nrRows - count($right), '');
        }

        $res = [];
        foreach ($left as $key => $value) {
            $res[] = [$value, $right[$key]];
        }

        return ['mobile_preview_rows' => $res];
    }

    /**
     * TODO: make it normal
     *
     * @return Language[]
     */
    private function getLanguages()
    {
        $languageRepository = $this->get('language');
        return [
            $languageRepository->get(Language::EN),
            $languageRepository->get(Language::RU),
        ];
    }

    /**
     * @return TranslatorInterface[]
     */
    private function getTranslators()
    {
        $translators = [];
        foreach ($this->getLanguages() as $language) {
            if ($this->translator->getLocale() === $language->getCode()) {
                $translators[] = $this->translator;
            } else {
                $translator = clone $this->translator;
                $translator->setLocale($language->getCode());
                $translators[] = $translator;
            }
        }

        return $translators;
    }
}
