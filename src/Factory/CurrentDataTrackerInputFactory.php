<?php

namespace Locastic\Loggastic\Factory;

use Locastic\Loggastic\Model\Input\CurrentDataTrackerInput;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;
use Locastic\Loggastic\Util\ClassUtils;

class CurrentDataTrackerInputFactory implements CurrentDataTrackerInputFactoryInterface
{
    public function create($item, ?array $normalizedData = []): CurrentDataTrackerInput
    {
        $currentDataTracker = new CurrentDataTrackerInput();

        $currentDataTracker->setObjectId($item->getId());
        $currentDataTracker->setData(json_encode($normalizedData, JSON_THROW_ON_ERROR));
        $currentDataTracker->setObjectClass(ClassUtils::getClass($item));

        return $currentDataTracker;
    }

    public function createFromCurrentDataTracker(CurrentDataTrackerInterface $currentDataTracker): CurrentDataTrackerInput
    {
        $currentDataTrackerInput = new CurrentDataTrackerInput();

        $currentDataTrackerInput->setObjectId($currentDataTracker->getObjectId());
        $currentDataTrackerInput->setData(json_encode($currentDataTracker->getData(), JSON_THROW_ON_ERROR));
        $currentDataTrackerInput->setObjectClass($currentDataTracker->getObjectClass());

        return $currentDataTrackerInput;
    }
}
