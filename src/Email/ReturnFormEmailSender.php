<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Email;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Filesystem\TemporaryFilesystem;
use Madcoders\SyliusRmaPlugin\Generator\OrderReturnFormPdfFileGeneratorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;


final class ReturnFormEmailSender implements ReturnFormEmailSenderInterface
{
    /** @var SenderInterface */
    private $emailSender;

    /** @var OrderReturnFormPdfFileGeneratorInterface */
    private $orderReturnFormPdfFileGenerator;

    /** @var TemporaryFilesystem */
    private $temporaryFilesystem;

    public function __construct(
        SenderInterface $emailSender,
        OrderReturnFormPdfFileGeneratorInterface $orderReturnFormPdfFileGenerator
    ) {
        $this->emailSender = $emailSender;
        $this->orderReturnFormPdfFileGenerator = $orderReturnFormPdfFileGenerator;
        $this->temporaryFilesystem = new TemporaryFilesystem();
    }

    public function sendReturnOrderFormEmail(
        OrderReturnInterface $orderReturn,
        ChannelInterface $channel,
        string $customerEmail
    ): void {
        $orderReturnFormPdf = $this->orderReturnFormPdfFileGenerator->generate($orderReturn);

        $this->temporaryFilesystem->executeWithFile(
            $orderReturnFormPdf->filename(),
            $orderReturnFormPdf->content(),
            function (string $filepath) use ($orderReturn, $customerEmail, $channel): void {
                $this->emailSender->send(Emails::RETURN_GENERATED, [$customerEmail], [
                    'orderReturn' => $orderReturn,
                    'channel' => $channel
                ], [$filepath]);
            }
        );
    }
}
