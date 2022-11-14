<?php

namespace Locastic\ActivityLog\Factory;

use Locastic\ActivityLog\Bridge\Elasticsearch\Context\ElasticsearchContextFactoryInterface;
use Locastic\ActivityLog\Model\ActivityLog;
use Locastic\ActivityLog\Model\ActivityLogInterface;
use Locastic\ActivityLog\Model\CurrentDataTracker;
use Locastic\ActivityLog\Model\CurrentDataTrackerInterface;
use Locastic\ActivityLog\Util\ClassUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class ActivityLogFactory implements ActivityLogFactoryInterface
{
    private RequestStack $requestStack;
    private Security $security;
    private ElasticsearchContextFactoryInterface $elasticsearchContextFactory;

    public function __construct(Security $security, RequestStack $requestStack, ElasticsearchContextFactoryInterface $elasticsearchContextFactory)
    {
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->elasticsearchContextFactory = $elasticsearchContextFactory;
    }

    public function createActivityLog($id, string $resourceClass, string $action, array $data = []): ActivityLogInterface
    {
        $activityLog = new ActivityLog();

        $activityLog->setData($data);
        $activityLog->setAction($action);
        $activityLog->setObjectClass($resourceClass);
        $activityLog->setObjectId($id);
        $activityLog->setUser($this->security->getUser());

        $request  = $this->requestStack->getCurrentRequest();
        if($request) {
            $activityLog->setRequestUrl($request->getMethod().' '.$request->getRequestUri());
        }

        return $activityLog;
    }

    public function createCurrentDataTracker($item, $normalizedData): CurrentDataTrackerInterface
    {
        $resourceClass = ClassUtils::getClass($item);
        $elasticContext = $this->elasticsearchContextFactory->createFromClassName($resourceClass);

        $currentDataTracker = new CurrentDataTracker();

        $currentDataTracker->setId($item->getId().'-'.$elasticContext->getShortName());
        $currentDataTracker->setObjectId($item->getId());
        $currentDataTracker->setDataFromArray($normalizedData);
        $currentDataTracker->setObjectClass($resourceClass);

        return $currentDataTracker;
    }
}
