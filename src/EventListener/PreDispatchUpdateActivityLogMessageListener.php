<?php

namespace Locastic\Loggastic\EventListener;

use Locastic\Loggastic\Event\PreDispatchActivityLogMessageEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PreDispatchActivityLogMessageListener
{
    public function __construct(
        private readonly Security $security,
        private readonly NormalizerInterface $normalizer,
        private readonly RequestStack $requestStack
    )
    {
    }

    public function __invoke(PreDispatchActivityLogMessageEvent $event)
    {
        $message = $event->getActivityLogMessage();

        // set user
        $user = $this->security->getUser();
        if ($user instanceof UserInterface) {
            $userInfo = $this->normalizer->normalize($user, 'array', ['groups' => 'activity_log']);
            $message->setUser($userInfo);
        }

        // set url
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $message->setRequestUrl($request->getMethod().' '.$request->getHttpHost().$request->getRequestUri());
        }
    }
}
