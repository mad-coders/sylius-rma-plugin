<?php

/*
 * This file is part of package:
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Security;

use Madcoders\SyliusRmaPlugin\Security\Exception\NotExistsException;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderReturnAuthorizerStorage implements OrderReturnAuthorizerStorageInterface
{
    private const SESSION_KEY = 'madcoders_rma_auth_order';

    /** @var SessionInterface */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function add(string $orderNumber, int $expiryTime = self::DEFAULT_EXPIRY_TIME): void
    {
        $data = $this->read();
        $data[$orderNumber] = [ 'expiryTime' => ((new \DateTime())->getTimestamp()+$expiryTime) ];
        $this->write($data);
    }

    public function remove(string $orderNumber): void
    {
        $data = $this->read();
        if (isset($data[$orderNumber])) {
            unset($data[$orderNumber]);
            $this->write($data);
        }
    }

    /**
     * @inheritdoc
     */
    public function get(string $orderNumber): array
    {
        $orderData = $this->read();

        if (!isset($orderData[$orderNumber]) || !is_array($orderData[$orderNumber])) {
            throw new NotExistsException(sprintf('Order number %s has not been found in Authorizer storage', $orderNumber));
        }

        if ($orderData[$orderNumber]['expiryTime'] < (new \DateTime())->getTimestamp()) {
            $this->remove($orderNumber);
            throw new NotExistsException(sprintf('Order number %s has not been found in Authorizer storage', $orderNumber));
        }

        return $orderData;
    }

    public function exists(string $orderNumber): bool
    {
        try {
            $this->get($orderNumber);
        } catch(NotExistsException $e) {
            return false;
        }

        return true;
    }

    private function read(): array
    {
        /** @var array|null $data */
        $data = $this->session->get(self::SESSION_KEY);

        return is_array($data) ? $data : [];
    }

    private function write(array $data): void
    {
        $this->session->set(self::SESSION_KEY, $data);
    }
}
