<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Generator;


use Knp\Snappy\GeneratorInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Model\OrderReturnFormPdf;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

final class OrderReturnFormPdfFileGenerator implements OrderReturnFormPdfFileGeneratorInterface
{
    private const FILE_EXTENSION = '.pdf';

    /** @var EngineInterface|Environment */
    private $templatingEngine;

    /** @var GeneratorInterface */
    private $pdfGenerator;

    /** @var FileLocatorInterface */
    private $fileLocator;

    /** @var string */
    private $template;

    /** @var string */
    private $orderReturnFormLogoPath;

    /**
     * OrderReturnFormPdfFileGenerator constructor.
     * @param EngineInterface|Environment $templatingEngine
     * @param GeneratorInterface $pdfGenerator
     * @param FileLocatorInterface $fileLocator
     * @param string $template
     * @param string $orderReturnFormLogoPath
     */
    public function __construct(
        $templatingEngine,
        GeneratorInterface $pdfGenerator,
        FileLocatorInterface $fileLocator,
        string $template,
        string $orderReturnFormLogoPath
    )
    {
        $this->templatingEngine = $templatingEngine;
        $this->pdfGenerator = $pdfGenerator;
        $this->fileLocator = $fileLocator;
        $this->template = $template;
        $this->orderReturnFormLogoPath = $orderReturnFormLogoPath;
    }

    public function generate(OrderReturnInterface $orderReturnForm): OrderReturnFormPdf
    {
        /** @var string $filename */
        $filename = str_replace('/', '_', $orderReturnForm->getReturnNumber()) . self::FILE_EXTENSION;

        $pdf = $this->pdfGenerator->getOutputFromHtml(
            $this->templatingEngine->render($this->template, [
                'orderReturnForm' => $orderReturnForm,
                'channel' => $orderReturnForm->getChannelCode(),
                'orderReturnFormLogoPath' => $this->fileLocator->locate($this->orderReturnFormLogoPath),
            ])
        );

        return new OrderReturnFormPdf($filename, $pdf);
    }
}
