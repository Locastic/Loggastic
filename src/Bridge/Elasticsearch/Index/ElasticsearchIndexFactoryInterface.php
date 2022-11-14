<?php

namespace Locastic\ActivityLog\Bridge\Elasticsearch\Index;

interface ElasticsearchIndexFactoryInterface
{
    // deletes activity log index if it exists and creates a new one
    public function recreateActivityLogIndex(string $className): void;
    // deletes current data tracker index if it exists and creates a new one
    public function recreateCurrentDataTrackerLogIndex(string $className): void;
    // creates the activity log index for the given class
    public function createActivityLogIndex(string $className): void;
    // creates the current data tracker index for the given class
    public function createCurrentDataTrackerLogIndex(string $className): void;
}
