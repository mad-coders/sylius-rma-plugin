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

namespace Madcoders\SyliusRmaPlugin\Email;

final class Emails
{
    public const AUTHCODE_GENERATED = 'authcode_generated';

    public const RETURN_GENERATED = 'return_generated';

    public const WITHDRAWAL_REQUESTED = 'withdrawal_requested';

    public const WITHDRAWAL_CONFIRMED = 'withdrawal_confirmed';

    public const WITHDRAWAL_FALLBACK = 'withdrawal_fallback';

    public const WITHDRAWAL_CANCELLED = 'withdrawal_cancelled';

    private function __construct()
    {
    }
}
