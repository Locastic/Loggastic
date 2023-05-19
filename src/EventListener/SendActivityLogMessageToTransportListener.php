<?php

namespace Locastic\ActivityLogs\EventListener;

use Locastic\ActivityLogs\Bridge\Elasticsearch\Context\Traits\ElasticNormalizationContextTrait;
use Locastic\ActivityLogs\Message\ActivityLogMessageInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SendActivityLogMessageToTransportListener
{
    use ElasticNormalizationContextTrait;

    private Security $security;
    private RequestStack $requestStack;
    private NormalizerInterface $normalizer;

    public function __construct(Security $security, RequestStack $requestStack, NormalizerInterface $normalizer)
    {
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->normalizer = $normalizer;
    }

    public function __invoke(SendMessageToTransportsEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if (!$message instanceof ActivityLogMessageInterface) {
            return;
        }

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
