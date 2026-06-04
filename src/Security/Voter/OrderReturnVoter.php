<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Security\Voter;

use Madcoders\SyliusRmaPlugin\Security\OrderReturnAuthorizerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Sylius RMA Plugin by MADCODERS
 *
 * @licence For the full copyright and license information, please view the LICENSE file
 *
 * Architects of this package:
 */
class OrderReturnVoter extends Voter implements VoterInterface
{
    public const ATTRIBUTE_RETURN = 'return';

    public const SUPPORTED_ATTRIBUTES = [self::ATTRIBUTE_RETURN];

    /** @var Security */
    private $security;

    /** @var OrderReturnAuthorizerInterface */
    private $orderReturnAuthenticator;

    public function __construct(Security $security, OrderReturnAuthorizerInterface $orderReturnAuthenticator)
    {
        $this->security = $security;
        $this->orderReturnAuthenticator = $orderReturnAuthenticator;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof OrderInterface) {
            return false;
        }

        if (!in_array($attribute, self::SUPPORTED_ATTRIBUTES)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$subject instanceof OrderInterface) {
            return false;
        }

        $user = $token->getUser();
        if ($user instanceof UserInterface && $this->security->isGranted('ROLE_USER')) {
            $orderUser = $subject->getUser();
            if (!$orderUser instanceof UserInterface) {
                return false;
            }

            return $orderUser->getUserIdentifier() === $user->getUserIdentifier();
        }

        return $this->orderReturnAuthenticator->isAllowed($subject);
    }
}
