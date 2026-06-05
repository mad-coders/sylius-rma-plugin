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

use Exception;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Filesystem\TemporaryFilesystem;
use Madcoders\SyliusRmaPlugin\Generator\OrderReturnFormPdfFileGeneratorInterface;
use Madcoders\SyliusRmaPlugin\Services\Configuration\ReturnAddressConfigurator;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;

final readonly class ReturnFormEmailSender implements ReturnFormEmailSenderInterface
{
    private TemporaryFilesystem $temporaryFilesystem;

    public function __construct(
        private SenderInterface $emailSender,
        private OrderReturnFormPdfFileGeneratorInterface $orderReturnFormPdfFileGenerator,
        private ReturnAddressConfigurator $returnAddressConfigurator,
        private bool $returnFormPdfEnabled = false,
    ) {
        $this->temporaryFilesystem = new TemporaryFilesystem();
    }

    /**
     * @throws Exception
     */
    public function sendReturnOrderFormEmail(
        OrderReturnInterface $orderReturn,
        ChannelInterface $channel,
        string $customerEmail,
    ): void {
        $returnAddress = $this->returnAddressConfigurator->getReturnAddressForReturnForm($channel);

        $emailData = [
            'orderReturn' => $orderReturn,
            'channel' => $channel,
            'returnAddress' => $returnAddress,
        ];

        // The return-form PDF is opt-in (madcoders_rma.return_form_pdf_enabled). When disabled,
        // the confirmation email is sent without the PDF attachment, so wkhtmltopdf is not required.
        if (!$this->returnFormPdfEnabled) {
            $this->emailSender->send(Emails::RETURN_GENERATED, [$customerEmail], $emailData);

            return;
        }

        $orderReturnFormPdf = $this->orderReturnFormPdfFileGenerator->generate($orderReturn);

        $this->temporaryFilesystem->executeWithFile(
            $orderReturnFormPdf->filename(),
            $orderReturnFormPdf->content(),
            function (string $filepath) use ($customerEmail, $emailData): void {
                $this->emailSender->send(Emails::RETURN_GENERATED, [$customerEmail], $emailData, [$filepath]);
            },
        );
    }
}
