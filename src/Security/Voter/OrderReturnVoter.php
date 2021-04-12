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
 * @copyright MADCODERS (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE file
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
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

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {
        if (!$subject instanceof OrderInterface) {
            return false;
        }

        if (!in_array($attribute, self::SUPPORTED_ATTRIBUTES)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
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

            return ($orderUser->getUsername() === $user->getUsername());
        }

        return $this->orderReturnAuthenticator->isAllowed($subject);
    }
}
