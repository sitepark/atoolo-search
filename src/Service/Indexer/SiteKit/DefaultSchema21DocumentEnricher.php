<?php

declare(strict_types=1);

namespace Atoolo\Search\Service\Indexer\SiteKit;

use Atoolo\Resource\Loader\SiteKitNavigationHierarchyLoader;
use Atoolo\Resource\Resource;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use DateTime;
use Solarium\Core\Query\DocumentInterface;

class DefaultSchema21DocumentEnricher implements DocumentEnricher
{
    public function __construct(
        private readonly SiteKitNavigationHierarchyLoader $navigationLoader
    ) {
    }

    public function enrichDocument(
        Resource $resource,
        DocumentInterface $doc,
        string $processId
    ): DocumentInterface {
        $doc->sp_id = $resource->getId();
        $doc->sp_name = $resource->getName();
        $doc->sp_anchor = $resource->getData('init.anchor');
        $doc->title = $resource->getData('base.title');
        $doc->description = $resource->getData('metadata.description');
        $doc->sp_objecttype = $resource->getObjectType();
        $doc->sp_canonical = true;
        $doc->crawl_process_id = $processId;

        $mediaUrl = $resource->getData('init.mediaUrl');
        if ($mediaUrl !== null) {
            $doc->id = $mediaUrl;
            $doc->url = $mediaUrl;
        } else {
            $doc->id = $resource->getLocation();
            $doc->url = $resource->getLocation();
        }

        $spContentType = [$resource->getObjectType()];
        if ($resource->getData('init.media') !== true) {
            $spContentType[] = 'article';
        }
        $contentSectionTypes = $resource->getData('init.contentSectionTypes');
        if (is_array($contentSectionTypes)) {
            $spContentType = array_merge($spContentType, $contentSectionTypes);
        }
        if ($resource->getData('base.teaser.image') !== null) {
            $spContentType[] = 'teaserImage';
        }
        if ($resource->getData('base.teaser.image.copyright') !== null) {
            $spContentType[] = 'teaserImageCopyright';
        }
        if ($resource->getData('base.teaser.headline') !== null) {
            $spContentType[] = 'teaserHeadline';
        }
        if ($resource->getData('base.teaser.text') !== null) {
            $spContentType[] = 'teaserText';
        }
        $doc->sp_contenttype = $spContentType;

        $locale = $this->getLocaleFromResource($resource);
        $lang = $this->toLangFromLocale($locale);
        $doc->sp_language = $lang;
        $doc->meta_content_language = $lang;

        $doc->sp_changed = $this->toDateTime(
            $resource->getData('init.changed')
        );
        $doc->sp_generated = $this->toDateTime(
            $resource->getData('init.generated')
        );
        $doc->sp_date = $this->toDateTime(
            $resource->getData('base.date')
        );

        $doc->sp_archive = $resource->getData('base.archive') ?? false;

        $headline = $resource->getData('metadata.headline');
        if (empty($headline)) {
            $headline = $resource->getData('base.teaser.headline');
        }
        if (empty($headline)) {
            $headline = $resource->getData('base.title');
        }
        $doc->sp_title = $headline;

        // However, the teaser heading, if specified, must be used for sorting
        $sortHeadline = $resource->getData('base.teaser.headline');
        if (empty($sortHeadline)) {
            $sortHeadline = $resource->getData('metadata.headline');
        }
        if (empty($sortHeadline)) {
            $sortHeadline = $resource->getData('base.title');
        }
        $doc->sp_sortvalue = $sortHeadline;

        $doc->sp_boost_keywords = $resource->getData('metadata.boostKeywords');

        $sites = $this->getParentSiteGroupIdList($resource);

        $navigationRoot = $this->navigationLoader->loadRoot(
            $resource->getLocation()
        );
        $siteGroupId = $navigationRoot->getData('init.siteGroup.id');
        if ($siteGroupId !== null) {
            $sites[] = $siteGroupId;
        }
        $doc->sp_site = array_unique($sites);

        $wktPrimaryList = $resource->getData('base.geo.wkt.primary');
        if (is_array($wktPrimaryList)) {
            $allWkt = [];
            foreach ($wktPrimaryList as $wkt) {
                $allWkt[] = $wkt;
            }
            if (count($allWkt) > 0) {
                $doc->sp_geo_points = $allWkt;
            }
        }

        $categoryList = $resource->getData('metadata.categories');
        if (is_array($categoryList)) {
            $categoryIdList = [];
            foreach ($categoryList as $category) {
                $categoryIdList[] = $category['id'];
            }
            $doc->sp_category = $categoryIdList;
        }

        $categoryPath = $resource->getData('metadata.categoriesPath');
        if (is_array($categoryPath)) {
            $categoryIdPath = [];
            foreach ($categoryPath as $category) {
                $categoryIdPath[] = $category['id'];
            }
            $doc->sp_category_path = $categoryIdPath;
        }

        $groupPath = $resource->getData('init.groupPath');
        $groupPathAsIdList = [];
        if (is_array($groupPath)) {
            foreach ($groupPath as $group) {
                $groupPathAsIdList[] = $group['id'];
            }
        }
        $doc->sp_group = $groupPathAsIdList[count($groupPathAsIdList) - 2];
        $doc->sp_group_path = $groupPathAsIdList;

        $schedulingList = $resource->getData('metadata.scheduling');
        if (is_array($schedulingList) && count($schedulingList) > 0) {
            $doc->sp_date = $this->toDateTime($schedulingList[0]['from']);
            $dateList = [];
            $contentTypeList = [];
            foreach ($schedulingList as $scheduling) {
                $contentTypeList[] = explode(' ', $scheduling['contentType']);
                $dateList[] = $this->toDateTime($scheduling['from']);
            }
            $doc->sp_contenttype = array_merge(
                $doc->sp_contenttype,
                ...$contentTypeList
            );
            $doc->sp_contenttype = array_unique($doc->sp_contenttype);

            $doc->sp_date_list = $dateList;
        }

        $contentType = $resource->getData('base.mime');
        if ($contentType === null) {
            $contentType = 'text/html; charset=UTF-8';
        }
        $doc->meta_content_type = $contentType;
        $doc->content = $resource->getData('searchindexdata.content');

        $accessType = $resource->getData('init.access.type');
        $groups = $resource->getData('init.access.groups');


        if ($accessType === 'allow' && is_array($groups)) {
            $doc->include_groups = array_map(
                fn($id): int => $this->idWithoutSignature($id),
                $groups
            );
        } elseif ($accessType === 'deny' && is_array($groups)) {
            $doc->exclude_groups = array_map(
                fn($id): int => $this->idWithoutSignature($id),
                $groups
            );
        } else {
            $doc->exclude_groups = ['none'];
            $doc->include_groups = ['all'];
        }

        $doc->sp_source = ['internal'];

        return $doc;
    }

    private function idWithoutSignature(string $id): int
    {
        $s = substr($id, -11);
        return (int)$s;
    }

    /* Customization
     * - https://gitlab.sitepark.com/customer-projects/fhdo/blob/develop/fhdo-module/src/publish/php/SP/Fhdo/Component/Content/DetailPage/StartletterIndexSupplier.php#L31
     * - https://gitlab.sitepark.com/apis/sitekit-php/blob/develop/php/SP/SiteKit/Component/Content/NewsdeskRss.php#L235
     * - https://gitlab.sitepark.com/customer-projects/fhdo/blob/develop/fhdo-module/src/publish/php/SP/Fhdo/Component/SearchMetadataExtension.php#L41
     * - https://gitlab.sitepark.com/customer-projects/paderborn/blob/develop/paderborn-module/src/publish/php/SP/Paderborn/Component/FscEntity.php#L67
     * - https://gitlab.sitepark.com/customer-projects/paderborn/blob/develop/paderborn-module/src/publish/php/SP/Paderborn/Component/FscContactPerson.php#L24
     * - https://gitlab.sitepark.com/customer-projects/stadtundland/blob/develop/stadtundland-module/src/publish/php/SP/Stadtundland/Component/ParkingSpaceExpose.php#L38
     * - https://gitlab.sitepark.com/customer-projects/stadtundland/blob/develop/stadtundland-module/src/publish/php/SP/Stadtundland/Component/Expose.php#L38
     * - https://gitlab.sitepark.com/customer-projects/stadtundland/blob/develop/stadtundland-module/src/publish/php/SP/Stadtundland/Component/PurchaseExpose.php#L38
     * - https://gitlab.sitepark.com/customer-projects/stuttgart/blob/develop/stuttgart-module/src/publish/php/SP/Stuttgart/Component/JobOffer.php#L29
     * - https://gitlab.sitepark.com/customer-projects/stuttgart/blob/develop/stuttgart-module/src/publish/php/SP/Stuttgart/Component/EventsCalendarExtension.php#L124
     * - https://gitlab.sitepark.com/ies-modules/citycall/blob/develop/citycall-module/src/main/php/src/SP/CityCall/Component/Intro.php#L51
     * - https://gitlab.sitepark.com/ies-modules/citycall/blob/develop/citycall-module/src/main/php/src/SP/CityCall/Controller/Environment.php#L76
     * - https://gitlab.sitepark.com/ies-modules/sitekit-real-estate/blob/develop/src/publish/php/SP/RealEstate/Component/Expose.php#L47
     */

    private function getLocaleFromResource(Resource $resource): string
    {

        $locale = $resource->getData('init.locale');
        if ($locale !== null) {
            return $locale;
        }
        $groupPath = $resource->getData('init.groupPath');
        $len = count($groupPath);
        if (is_array($groupPath)) {
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

    private function toDateTime(?int $timestamp): ?DateTime
    {
        if ($timestamp === null) {
            return null;
        }
        if ($timestamp <= 0) {
            return null;
        }

        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);
        return $dateTime;
    }

    private function getParentSiteGroupIdList(Resource $resource): array
    {
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
     * @return array
     */
    private function getNavigationParents(Resource $resource): array
    {
        $parents = $resource->getData('base.trees.navigation.parents');
        return $parents ?? [];
    }
}
