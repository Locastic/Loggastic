<?php

namespace Locastic\Loggastic\Factory;

use Locastic\Loggastic\Model\Input\CurrentDataTrackerInput;
use Locastic\Loggastic\Model\Output\CurrentDataTrackerInterface;

interface CurrentDataTrackerInputFactoryInterface
{
    public function create($item, ?array $normalizedData = []): CurrentDataTrackerInput;

    public function createFromCurrentDataTracker(CurrentDataTrackerInterface $currentDataTracker): CurrentDataTrackerInput;
}
