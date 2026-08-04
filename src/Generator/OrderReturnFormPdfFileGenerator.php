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

namespace Madcoders\SyliusRmaPlugin\Generator;

use Exception;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Model\OrderReturnFormPdf;
use Madcoders\SyliusRmaPlugin\Services\Configuration\ReturnAddressConfigurator;
use Madcoders\SyliusRmaPlugin\Services\Pdf\PdfGeneratorInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Twig\Environment;

final class OrderReturnFormPdfFileGenerator implements OrderReturnFormPdfFileGeneratorInterface
{
    private const FILE_EXTENSION = '.pdf';

    /**
     * OrderReturnFormPdfFileGenerator constructor.
     *
     * @param Environment            $templatingEngine
     * @param RepositoryInterface<ChannelInterface> $channelsRepository
     */
    public function __construct(
        private $templatingEngine,
        private readonly PdfGeneratorInterface $pdfGenerator,
        private readonly FileLocatorInterface $fileLocator,
        private readonly string $template,
        private readonly string $orderReturnFormLogoPath,
        private readonly ReturnAddressConfigurator $returnAddressConfigurator,
        private readonly RepositoryInterface $channelsRepository,
    ) {
    }

    /**
     * @throws Exception
     */
    public function generate(OrderReturnInterface $orderReturnForm): OrderReturnFormPdf
    {
        $channelCode = $orderReturnForm->getChannelCode();
        $channel = $this->channelsRepository->findOneBy(['code' => $channelCode]);
        if (!$channel instanceof ChannelInterface) {
            throw new \InvalidArgumentException(sprintf('Channel must implement %s', ChannelInterface::class));
        }
        $returnAddress = $this->returnAddressConfigurator->getReturnAddressForReturnForm($channel);

        $filename = str_replace('/', '_', $orderReturnForm->getReturnNumber()) . self::FILE_EXTENSION;

        $pdf = $this->pdfGenerator->generateFromHtml(
            $this->templatingEngine->render($this->template, [
                'orderReturnForm' => $orderReturnForm,
                'channel' => $orderReturnForm->getChannelCode(),
                'orderReturnFormLogoPath' => $this->fileLocator->locate($this->orderReturnFormLogoPath),
                'returnAddress' => $returnAddress,
            ]),
        );

        return new OrderReturnFormPdf($filename, $pdf);
    }
}
