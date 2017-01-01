<?php

namespace AppBundle\Entity\ItemsList;

use AppBundle\Entity\Language\Language;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;
use Utils\DB\SQL;

class ItemsListRepository
{
    /** @var SQL */
    private $sql;
    /** @var Router */
    private $router;
    /** @var Translator */
    private $translator;
    /** @var Language */
    private $language;

    /** @var int */
    private $language1Id = 1;
    /** @var int */
    private $language2Id = 2;
    /** @var string */
    private $authorPropertyName = 'name';

    /**
     * @param SQL $sql
     * @param Router $router
     * @param Translator $translator
     * @param Language $language
     */
    public function __construct(
        SQL $sql,
        Router $router,
        Translator $translator,
        Language $language
    ) {
        $this->sql = $sql;
        $this->router = $router;
        $this->translator = $translator;
        $this->language = $language;
    }

    /**
     * @param int|null $limit
     * @param int $offset
     * @return ItemsList
     */
    public function getAuthorsList($limit = null, $offset = 0)
    {
        $items = $this
            ->sql
            ->prepare(
                <<<'SQL'
                SELECT
                    a.id,
                    alps.property_value,
                    mnba.nr_books
                FROM
                    authors a
                    JOIN author_language_property alp
                    JOIN author_language_properties alps
                        ON a.id = alps.author_id
                        AND alp.id = alps.property_id
                    JOIN mat_nr_books_authors mnba
                        ON mnba.author_id = a.id
                WHERE
                    alp.property_name = :property_name
                    AND alps.language_id = :language_id
                    AND mnba.language1_id = :language1_id
                    AND mnba.language2_id = :language2_id
                ORDER BY
                    mnba.nr_books DESC,
                    a.id
                LIMIT :LIMIT OFFSET :offset
SQL
            )
            ->execute([
                'property_name' => $this->authorPropertyName,
                'language_id' => $this->language->getId(),
                'language1_id' => $this->language1Id,
                'language2_id' => $this->language2Id,
                'LIMIT' => $limit,
                'offset' => $offset,
            ])
            ->map(function (array $row) {
                $url = $this->router->generate(
                    'author',
                    ['authorId' => $row['id']]
                );
                return new ListItem(
                    $url,
                    $row['property_value'],
                    $row['nr_books']
                );
            });

        return new ItemsList(
            false,
            $this->translator->trans('items-header-authors'),
            $items,
            $this->getUploadUrlPrefix('author'),
            false
        );
    }

    /**
     * @param int|null $limit
     * @param int $offset
     * @return ItemsList
     */
    public function getSectionsList($limit = null, $offset = 0)
    {
        $items = $this
            ->sql
            ->prepare(
                <<<'SQL'
                SELECT
                    s.id,
                    st.title,
                    mnbs.nr_books
                FROM
                    sections s
                    JOIN section_titles st
                        ON s.id = st.section_id
                    JOIN mat_nr_books_sections mnbs
                        ON mnbs.section_id = s.id
                WHERE
                    st.language_id = :language_id
                    AND mnbs.language1_id = :language1_id
                    AND mnbs.language2_id = :language2_id
                ORDER BY
                    mnbs.nr_books DESC,
                    s.id
                LIMIT :LIMIT OFFSET :offset
SQL
            )
            ->execute([
                'language_id' => $this->language->getId(),
                'language1_id' => $this->language1Id,
                'language2_id' => $this->language2Id,
                'LIMIT' => $limit,
                'offset' => $offset,
            ])
            ->map(function (array $row) {
                $url = $this->router->generate(
                    'section',
                    ['sectionId' => $row['id']]
                );
                return new ListItem($url, $row['title'], $row['nr_books']);
            });

        $header = $this->translator->trans('items-header-sections');

        return new ItemsList(
            false,
            $header,
            $items,
            $this->getUploadUrlPrefix('section'),
            false
        );
    }

    public function getBooksList($limit = null, $offset = 0)
    {
        $items = $this
            ->sql
            ->prepare(
                <<<'SQL'
                SELECT
                    p.id,
                    b_left.title AS title_left,
                    b_right.title AS title_right
                FROM
                    parallangos p
                    JOIN books b_left
                        ON b_left.id = p.left_book_id
                    LEFT JOIN books b_right
                        ON b_right.id = p.right_book_id
                    JOIN authors a
                        ON a.id = b_left.author_id
                    JOIN author_language_property alp
                    JOIN author_language_properties alps
                        ON alps.author_id = a.id
                WHERE
                    alp.property_name = :property_name
                    AND alps.language_id = :language_id
                    AND b_left.language_id = :language1_id
                    AND b_right.language_id = :language2_id
SQL
            )
            ->execute([
                'property_name' => $this->authorPropertyName,
                'language_id' => $this->language->getId(),
                'language1_id' => $this->language1Id,
                'language2_id' => $this->language2Id,
            ])
            ->map(function (array $row) {
                $url = $this->router->generate(
                    'parallango',
                    ['parallangoId' => $row['id']]
                );
                return new ListItem($url, $row['']);
            });
    }

    private function getUploadUrlPrefix($itemsType)
    {
        $dummyOffset = 0;
        $url = $this->router->generate('items', [
            'itemsType' => $itemsType,
            'offset' => $dummyOffset,
        ]);

        return _preg_match('#^(.*/)\\d+/$#', $url)[1];
    }
}
