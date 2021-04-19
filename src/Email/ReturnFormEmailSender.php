<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Email;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Filesystem\TemporaryFilesystem;
use Madcoders\SyliusRmaPlugin\Generator\OrderReturnFormPdfFileGeneratorInterface;
use Madcoders\SyliusRmaPlugin\Services\Configuration\ReturnAddressConfigurator;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Exception;

final class ReturnFormEmailSender implements ReturnFormEmailSenderInterface
{
    /** @var SenderInterface */
    private $emailSender;

    /** @var OrderReturnFormPdfFileGeneratorInterface */
    private $orderReturnFormPdfFileGenerator;

    /** @var ReturnAddressConfigurator */
    private $returnAddressConfigurator;

    /** @var TemporaryFilesystem */
    private $temporaryFilesystem;

    public function __construct(
        SenderInterface $emailSender,
        OrderReturnFormPdfFileGeneratorInterface $orderReturnFormPdfFileGenerator,
        ReturnAddressConfigurator $returnAddressConfigurator
    )
    {
        $this->emailSender = $emailSender;
        $this->orderReturnFormPdfFileGenerator = $orderReturnFormPdfFileGenerator;
        $this->returnAddressConfigurator = $returnAddressConfigurator;
        $this->temporaryFilesystem = new TemporaryFilesystem();
    }

    /**
     * @param OrderReturnInterface $orderReturn
     * @param ChannelInterface $channel
     * @param string $customerEmail
     * @throws Exception
     */
    public function sendReturnOrderFormEmail(
        OrderReturnInterface $orderReturn,
        ChannelInterface $channel,
        string $customerEmail
    ): void {
        $orderReturnFormPdf = $this->orderReturnFormPdfFileGenerator->generate($orderReturn);
        if (!$returnAddress = $this->returnAddressConfigurator->getReturnAddressForReturnForm($channel))
        {
            throw new Exception('Address not defined for Selected channel');
        }

        $this->temporaryFilesystem->executeWithFile(
            $orderReturnFormPdf->filename(),
            $orderReturnFormPdf->content(),
            function (string $filepath) use ($orderReturn, $customerEmail, $channel, $returnAddress): void {
                $this->emailSender->send(Emails::RETURN_GENERATED, [$customerEmail], [
                    'orderReturn' => $orderReturn,
                    'channel' => $channel,
                    'returnAddress' => $returnAddress
                ], [$filepath]);
            }
        );
    }
}
