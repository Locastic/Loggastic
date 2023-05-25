<?php

namespace Locastic\Loggastic\Identifier;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;

// Used for setting collection keys in activity logs data
final class LogIdentifierExtractor implements LogIdentifierExtractorInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getIdentifierValue(object $object): int|string
    {
        $identifier = $this->entityManager->getClassMetadata(ClassUtils::getClass($object))->getSingleIdentifierFieldName();
        $identifierGetter = 'get'.$identifier;

        return $object->$identifierGetter();
    }
}
