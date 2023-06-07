<?php

namespace Locastic\Loggastic\Identifier;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

// Used for setting collection keys in activity logs data
final class LogIdentifierExtractor implements LogIdentifierExtractorInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getIdentifierValue(object $object): int|string|null
    {
        try {
            $identifier = $this->entityManager->getClassMetadata(ClassUtils::getClass($object))->getSingleIdentifierFieldName();
            $identifierGetter = 'get' . $identifier;

            return $object->$identifierGetter();
        } catch (MappingException|HandlerFailedException $e) {
            // object not mapped to doctrine, try with getId method or return null
            return method_exists($object, 'getId') ? $object->getId() : null;
        }
    }
}
