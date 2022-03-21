<?php

namespace App\Serializer\Normalizer;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class UserNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'USER_NORMALIZER_ALREADY_CALLED';

    public function __construct(private Security $security)
    {
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        /* @var User $object */
        $isOwner = $this->userIsOwner($object);

        if ($isOwner) {
            $context['groups'][] = User::OWNER_READ;
        }

        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);
        $data['isMe'] = $isOwner;

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

    private function userIsOwner(User $object): bool
    {
        /** @var User|null $authentidatedUser */
        $authentidatedUser = $this->security->getUser();

        if (!$authentidatedUser) {
            return false;
        }

        return $authentidatedUser->getEmail() === $object->getEmail();
    }
}
