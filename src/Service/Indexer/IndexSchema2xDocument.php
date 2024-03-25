<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer;

use DateTime;
use Solarium\QueryType\Update\Query\Document;

class IndexSchema2xDocument extends Document implements IndexDocument
{
    private const INHERITED_FIELDS = [
        'fields',
        'modifiers',
        'fieldBoosts'
    ];

    private const META_FIELDS = [
        'metaString',
        'metaText',
        'metaBool'
    ];

    public ?string $sp_id = null;
    public ?string $sp_name = null;
    public ?string $sp_anchor = null;
    public ?string $title = null;
    public ?string $description = null;
    public ?string $sp_objecttype = null;
    public ?bool $sp_canonical = null;
    public ?string $crawl_process_id = null;
    public ?string $id = null;
    public ?string $url = null;
    public ?string $contenttype = null;
    /**
     * @var string[]
     */
    public ?array $sp_contenttype = null;
    public ?string $sp_language = null;
    public ?string $meta_content_language = null;
    public ?string $meta_content_type = null;
    public ?DateTime $sp_changed = null;
    public ?DateTime $sp_generated = null;
    public ?DateTime $sp_date = null;
    public ?DateTime $sp_date_from = null;
    public ?DateTime $sp_date_to = null;
    /**
     * @var DateTime[]
     */
    public ?array $sp_date_list = null;
    public ?bool $sp_archive = null;
    public ?string $sp_title = null;
    public ?string $sp_sortvalue = null;
    /**
     * @var string[]
     */
    public ?array $keywords = null;
    public ?string $sp_boost_keywords = null;
    /**
     * @var string[]
     */
    public ?array $sp_site = null;
    /**
     * @var string[]
     */
    public ?array $sp_geo_points = null;
    /**
     * @var string[]
     */
    public ?array $sp_category = null;
    /**
     * @var string[]
     */
    public ?array $sp_category_path = null;
    public ?int $sp_group = null;
    /**
     * @var int[]
     */
    public ?array $sp_group_path = null;
    public ?string $content = null;
    /**
     * @var string[]
     */
    public ?array $include_groups = null;
    /**
     * @var string[]
     */
    public ?array $exclude_groups = null;
    /**
     * @var string[]
     */
    public ?array $sp_source = null;

    /**
     * @var string[]
     */
    public ?array $sp_citygov_phone = null;

    /**
     * @var string[]
     */
    public ?array $sp_citygov_email = null;

    public ?string $sp_citygov_address = null;

    public ?string $sp_citygov_startletter = null;

    /**
     * @var string[]
     */
    public ?array $sp_citygov_organisationtoken = null;

    public ?int $sp_organisation = null;

    public ?string $sp_citygov_firstname = null;

    public ?string $sp_citygov_lastname = null;

    /**
     * List of Organisation Ids
     * @var int[]
     */
    public ?array $sp_organisation_path = null;

    /**
     * List of Organisationnames
     * @var string[]
     */
    public ?array $sp_citygov_organisation = null;

    /**
     * List of Productnames
     * @var string[]
     */
    public ?array $sp_citygov_product = null;

    public ?string $sp_citygov_function = null;
    /**
     * @var array<string,string|string[]>
     */
    private array $metaString = [];

    /**
     * @var array<string,string|string[]>
     */
    private array $metaText = [];

    /**
     * @var array<string,bool>
     */
    private array $metaBool = [];

    /**
     * @param string|string[] $value
     */
    public function setMetaString(string $name, string|array $value): void
    {
        $this->metaString['sp_meta_string_' . $name] = $value;
    }

    /**
     * @param string|string[] $value
     */
    public function setMetaText(string $name, string|array $value): void
    {
        $this->metaText['sp_meta_text_' . $name] = $value;
    }

    public function setMetaBool(string $name, bool $value): void
    {
        $this->metaBool['sp_meta_bool_' . $name] = $value;
    }

    /**
     * @return array<String, mixed>
     */
    public function getFields(): array
    {
        return [
            ...array_filter(
                get_object_vars($this),
                fn($value, $key) => !is_null($value)
                    && !in_array($key, self::INHERITED_FIELDS, true)
                    && !in_array($key, self::META_FIELDS, true),
                ARRAY_FILTER_USE_BOTH
            ),
            ... $this->metaString,
            ... $this->metaText,
            ... $this->metaBool,
        ];
    }
}
