<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer\SiteKit;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\Loader\SiteKitNavigationHierarchyLoader;
use Atoolo\Resource\Resource;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\ContentCollector;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use DateTime;
use Exception;

/**
 * @phpstan-type Phone array{
 *     countryCode?:string,
 *     areaCode?:string,
 *     localNumber?:string
 * }
 * @phpstan-type PhoneData array{phone:Phone}
 * @phpstan-type PhoneList array<PhoneData>
 * @phpstan-type Email array{email:string}
 * @phpstan-type EmailList array<Email>
 * @phpstan-type ContactData array{
 *     phoneList?:PhoneList,
 *     emailList:EmailList
 * }
 * @phpstan-type AddressData array{
 *     buildingName?:string,
 *     street?:string,
 *     postOfficeBoxData?: array{
 *          buildingName?:string
 *     }
 * }
 * @phpstan-type ContactPoint array{
 *     contactData?:ContactData,
 *     addressData?:AddressData
 * }
 * @implements DocumentEnricher<IndexSchema2xDocument>
 */
class DefaultSchema2xDocumentEnricher implements DocumentEnricher
{
    public function __construct(
        private readonly SiteKitNavigationHierarchyLoader $navigationLoader,
        private readonly ContentCollector $contentCollector
    ) {
    }

    public function isIndexable(Resource $resource): bool
    {
        $noIndex = $resource->getData()->getBool('init.noIndex');
        return $noIndex !== true;
    }

    /**
     * @throws DocumentEnrichingException
     */
    public function enrichDocument(
        Resource $resource,
        IndexDocument $doc,
        string $processId
    ): IndexDocument {

        $data = $resource->getData();
        $init = new DataBag($data->getAssociativeArray('init'));
        $base = new DataBag($data->getAssociativeArray('base'));
        $metadata = new DataBag($data->getAssociativeArray('metadata'));

        $doc->sp_id = $resource->getId();
        $doc->sp_name = $resource->getName();
        $doc->sp_anchor = $init->getString('anchor');
        $doc->title = $base->getString('title');
        $doc->description = $metadata->getString('description');

        if (empty($doc->description)) {
            $doc->description = $resource->getData()->getString(
                'metadata.intro'
            );
        }
        $doc->sp_objecttype = $resource->getObjectType();
        $doc->sp_canonical = true;
        $doc->crawl_process_id = $processId;

        $url = $init->getString('mediaUrl')
            ?: $init->getString('url');
        $doc->id = $url;
        $doc->url = $url;

        /** @var string[] $spContentType */
        $spContentType = [$resource->getObjectType()];
        if ($init->getBool('media') !== true) {
            $spContentType[] = 'article';
        }
        $contentSectionTypes = $init->getArray('contentSectionTypes');
        $spContentType = array_merge($spContentType, $contentSectionTypes);

        if ($base->has('teaser.image')) {
            $spContentType[] = 'teaserImage';
        }
        if ($base->has('teaser.image.copyright')) {
            $spContentType[] = 'teaserImageCopyright';
        }
        if ($base->has('teaser.headline')) {
            $spContentType[] = 'teaserHeadline';
        }
        if ($base->has('teaser.text')) {
            $spContentType[] = 'teaserText';
        }
        $doc->sp_contenttype = $spContentType;

        $locale = $this->getLocaleFromResource($resource);
        $lang = $this->toLangFromLocale($locale);
        $doc->sp_language = $lang;
        $doc->meta_content_language = $lang;

        $doc->sp_changed = $this->toDateTime(
            $init->getInt('changed')
        );
        $doc->sp_generated = $this->toDateTime(
            $init->getInt('generated')
        );
        $doc->sp_date = $this->toDateTime(
            $base->getInt('date')
        );

        $doc->sp_archive = $base->getBool('archive');

        $headline = $metadata->getString('headline')
            ?: $base->getString('teaser.headline')
            ?: $base->getString('title');
        $doc->sp_title = $headline;

        $sortHeadline = $base->getString('teaser.headline')
            ?: $metadata->getString('headline')
            ?: $base->getString('title');
        $doc->sp_sortvalue = $sortHeadline;

        /** @var string[] $keyword */
        $keyword = $metadata->getArray('keywords');
        $doc->keywords = $keyword;

        $doc->sp_boost_keywords = implode(
            ' ',
            $metadata->getArray('boostKeywords')
        );

        try {
            $sites = $this->getParentSiteGroupIdList($resource);

            $navigationRoot = $this->navigationLoader->loadRoot(
                $resource->getLocation()
            );

            $siteGroupId = $navigationRoot->getData()->getInt(
                'init.siteGroup.id'
            );
            if ($siteGroupId !== 0) {
                $sites[] = (string)$siteGroupId;
            }
            $doc->sp_site = array_unique($sites);
        } catch (Exception $e) {
            throw new DocumentEnrichingException(
                $resource->getLocation(),
                'Unable to set sp_site: ' . $e->getMessage(),
                0,
                $e
            );
        }

        /** @var string[] $wktPrimaryList */
        $wktPrimaryList = $base->getArray('geo.wkt.primary');
        if (!empty($wktPrimaryList)) {
            $doc->sp_geo_points = $wktPrimaryList;
        }

        /** @var array<array{id: int}> $categoryList */
        $categoryList = $metadata->getArray('categories');
        if (!empty($categoryList)) {
            $categoryIdList = [];
            foreach ($categoryList as $category) {
                $categoryIdList[] = (string)$category['id'];
            }
            $doc->sp_category = $categoryIdList;
        }

        /** @var array<array{id: int}> $categoryPath */
        $categoryPath = $metadata->getArray('categoriesPath');
        if (!empty($categoryPath)) {
            $categoryIdPath = [];
            foreach ($categoryPath as $category) {
                $categoryIdPath[] = (string)$category['id'];
            }
            $doc->sp_category_path = $categoryIdPath;
        }

        /** @var array<array{id: int}> $groupPath */
        $groupPath = $init->getArray('groupPath');
        $groupPathAsIdList = [];
        foreach ($groupPath as $group) {
            $groupPathAsIdList[] = $group['id'];
        }

        if (count($groupPathAsIdList) > 2) {
            $doc->sp_group = $groupPathAsIdList[count($groupPathAsIdList) - 2];
        }
        $doc->sp_group_path = $groupPathAsIdList;

        /** @var array<array{from:int, contentType:string}> $schedulingList */
        $schedulingList = $metadata->getArray('scheduling');
        if (!empty($schedulingList)) {
            $doc->sp_date = $this->toDateTime($schedulingList[0]['from']);
            $dateList = [];
            $contentTypeList = [];
            foreach ($schedulingList as $scheduling) {
                $contentTypeList[] = explode(' ', $scheduling['contentType']);
                $from = $this->toDateTime($scheduling['from']);
                if ($from !== null) {
                    $dateList[] = $from;
                }
            }
            $doc->sp_contenttype = array_merge(
                $doc->sp_contenttype,
                ...$contentTypeList
            );
            $doc->sp_contenttype = array_unique($doc->sp_contenttype);

            $doc->sp_date_list = $dateList;
        }

        $contentType = $base->getString(
            'mime',
            'text/html; charset=UTF-8'
        );
        $doc->meta_content_type = $contentType;

        $accessType = $init->getString('access.type');

        /** @var string[] $groups */
        $groups = $init->getArray('access.groups');

        if ($accessType === 'allow' && !empty($groups)) {
            $doc->include_groups = array_map(
                fn($id): string => (string)$this->idWithoutSignature($id),
                $groups
            );
        } elseif ($accessType === 'deny' && !empty($groups)) {
            $doc->exclude_groups = array_map(
                fn($id): string => (string)$this->idWithoutSignature($id),
                $groups
            );
        } else {
            $doc->exclude_groups = ['none'];
            $doc->include_groups = ['all'];
        }

        $doc->sp_source = ['internal'];

        return $this->enrichContent($resource, $doc);
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param E $doc
     * @return E
     */
    private function enrichContent(
        Resource $resource,
        IndexDocument $doc,
    ): IndexDocument {

        $content = [];
        $content[] = $resource->getData()->getString(
            'searchindexdata.content'
        );

        $content[] = $this->contentCollector->collect(
            $resource->getData()->getArray('content')
        );

        /** @var ContactPoint $contactPoint */
        $contactPoint = $resource->getData()->getArray('metadata.contactPoint');
        $content[] = $this->contactPointToContent($contactPoint);

        /** @var array<array{name?:string}> $categories */
        $categories = $resource->getData()->getArray('metadata.categories');
        foreach ($categories as $category) {
            $content[] = $category['name'] ?? '';
        }

        $cleanContent = preg_replace(
            '/\s+/',
            ' ',
            implode(' ', $content)
        );

        $doc->content = trim($cleanContent ?? '');

        return $doc;
    }

    /**
    * @param ContactPoint $contactPoint
    * @return string
     */
    private function contactPointToContent(array $contactPoint): string
    {
        if (empty($contactPoint)) {
            return '';
        }

        $content = [];
        foreach (($contactPoint['contactData']['phoneList'] ?? []) as $phone) {
            $countryCode = $phone['phone']['countryCode'] ?? '';
            if (
                !empty($countryCode) &&
                !in_array($countryCode, $content, true)
            ) {
                $content[] = '+' . $countryCode;
            }
            $areaCode = $phone['phone']['areaCode'] ?? '';
            if (!empty($areaCode) && !in_array($areaCode, $content, true)) {
                $content[] = $areaCode;
                $content[] = '0' . $areaCode;
            }
            $content[] = $phone['phone']['localNumber'] ?? '';
        }
        foreach ($contactPoint['contactData']['emailList'] ?? [] as $email) {
            $content[] = $email['email'];
        }

        if (isset($contactPoint['addressData'])) {
            $addressData = $contactPoint['addressData'];
            $content[] = $addressData['street'] ?? '';
            $content[] = $addressData['buildingName'] ?? '';
            $content[] = $addressData['postOfficeBoxData']['buildingName']
                    ?? '';
        }

        return implode(' ', $content);
    }

    private function idWithoutSignature(string $id): int
    {
        $s = substr($id, -11);
        return (int)$s;
    }

    private function getLocaleFromResource(Resource $resource): string
    {

        $locale = $resource->getData()->getString('init.locale');
        if ($locale !== '') {
            return $locale;
        }

        /** @var array<array{locale: ?string}> $groupPath */
        $groupPath = $resource->getData()->getArray('init.groupPath');
        if (!empty($groupPath)) {
            $len = count($groupPath);
            for ($i = $len - 1; $i >= 0; $i--) {
                $group = $groupPath[$i];
                if (isset($group['locale'])) {
                    return $group['locale'];
                }
            }
        }

        return 'de_DE';
    }

    private function toLangFromLocale(string $locale): string
    {
        if (str_contains($locale, '_')) {
            $parts = explode('_', $locale);
            return $parts[0];
        }
        return $locale;
    }

    private function toDateTime(int $timestamp): ?DateTime
    {
        if ($timestamp <= 0) {
            return null;
        }

        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);
        return $dateTime;
    }

    /**
     * @param Resource $resource
     * @return string[]
     */
    private function getParentSiteGroupIdList(Resource $resource): array
    {
        /** @var array<array{siteGroup: array{id: ?string}}> $parents */
        $parents = $this->getNavigationParents($resource);
        if (empty($parents)) {
            return [];
        }

        $siteGroupIdList = [];
        foreach ($parents as $parent) {
            if (isset($parent['siteGroup']['id'])) {
                $siteGroupIdList[] = $parent['siteGroup']['id'];
            }
        }

        return $siteGroupIdList;
    }

    /**
     * @return array<string,mixed>
     */
    private function getNavigationParents(Resource $resource): array
    {
        return $resource->getData()->getAssociativeArray(
            'base.trees.navigation.parents'
        );
    }
}
