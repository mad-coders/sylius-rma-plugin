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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use Behat\Mink\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Service\RecordingPdfGenerator;
use Webmozart\Assert\Assert;

/**
 * Covers the printable return form: the success-page link (`madcoders_rma_return_form_print`) and
 * the account history action (`madcoders_rma_shop_account_return_order_download`). Runs with the
 * PDF enabled in the `test_pdf` environment (Behat profile "pdf") and with it disabled in `test`.
 */
final class ReturnFormPdfContext implements Context
{
    /**
     * @param \ArrayAccess<string, mixed> $minkParameters
     */
    public function __construct(
        private readonly Session $session,
        private readonly \ArrayAccess $minkParameters,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly RecordingPdfGenerator $pdfGenerator,
    ) {
    }

    /**
     * @BeforeScenario
     */
    public function resetRecordedPdf(): void
    {
        $this->pdfGenerator->reset();
    }

    /**
     * @When I follow the link to print the return form
     */
    public function iFollowTheLinkToPrintTheReturnForm(): void
    {
        $this->session->getPage()->clickLink($this->printLinkLabel());
    }

    /**
     * @When I open the return form print page for return :returnNumber
     */
    public function iOpenTheReturnFormPrintPage(string $returnNumber): void
    {
        $this->visitRoute('madcoders_rma_return_form_print', $returnNumber);
    }

    /**
     * @When I download the return form of return :returnNumber from my account
     */
    public function iDownloadTheReturnFormFromMyAccount(string $returnNumber): void
    {
        $this->visitRoute('madcoders_rma_shop_account_return_order_download', $returnNumber);
    }

    /**
     * @Then I should see the link to print the return form
     */
    public function iShouldSeeTheLinkToPrintTheReturnForm(): void
    {
        Assert::notNull(
            $this->session->getPage()->findLink($this->printLinkLabel()),
            'Expected a link to print the return form, but none was found.',
        );
    }

    /**
     * @Then I should not see the link to print the return form
     */
    public function iShouldNotSeeTheLinkToPrintTheReturnForm(): void
    {
        Assert::null(
            $this->session->getPage()->findLink($this->printLinkLabel()),
            'Expected no link to print the return form, but one was found.',
        );
    }

    /**
     * @Then I should receive the return form PDF named :filename
     */
    public function iShouldReceiveTheReturnFormPdfNamed(string $filename): void
    {
        Assert::same($this->session->getStatusCode(), 200);
        Assert::startsWith((string) $this->session->getResponseHeader('Content-Type'), 'application/pdf');

        $disposition = (string) $this->session->getResponseHeader('Content-Disposition');
        Assert::startsWith($disposition, 'attachment');
        Assert::contains($disposition, sprintf('filename=%s', $filename));

        Assert::same($this->session->getDriver()->getContent(), RecordingPdfGenerator::PDF_BODY);
    }

    /**
     * @Then I should not receive a return form PDF
     */
    public function iShouldNotReceiveAReturnFormPdf(): void
    {
        Assert::false(str_starts_with((string) $this->session->getResponseHeader('Content-Type'), 'application/pdf'));
        Assert::null($this->pdfGenerator->lastRenderedHtml(), 'No return form PDF should have been rendered.');
    }

    /**
     * @Then the printed return form should contain :text
     */
    public function thePrintedReturnFormShouldContain(string $text): void
    {
        $html = $this->pdfGenerator->lastRenderedHtml();
        Assert::notNull($html, 'No return form PDF was rendered.');
        Assert::contains($html, $text);
    }

    /**
     * @Then I should be told that the return form PDF is disabled
     */
    public function iShouldBeToldThatTheReturnFormPdfIsDisabled(): void
    {
        $this->assertPageContainsTranslation('madcoders_rma.ui.return.pdf_disabled');
    }

    /**
     * @Then I should be told that I have no access to this return
     */
    public function iShouldBeToldThatIHaveNoAccessToThisReturn(): void
    {
        $this->assertPageContainsTranslation('madcoders_rma.ui.return.user_not_privileges_to_this_order');
    }

    /**
     * Visits a shop route on Mink's base_url, like the Sylius page objects do: a bare path would
     * resolve against BrowserKit's default host and drop the signed-in customer's session cookie.
     */
    private function visitRoute(string $route, string $returnNumber): void
    {
        $baseUrl = $this->minkParameters['base_url'] ?? '';
        Assert::string($baseUrl);

        $path = $this->router->generate($route, ['_locale' => 'en_US', 'returnNumber' => $returnNumber]);

        $this->session->visit(rtrim($baseUrl, '/') . $path);
    }

    private function assertPageContainsTranslation(string $key): void
    {
        $message = $this->translator->trans($key, [], null, 'en_US');
        Assert::contains($this->session->getPage()->getText(), $message);
    }

    private function printLinkLabel(): string
    {
        return $this->translator->trans('madcoders_rma.ui.success.print_order_return_form', [], null, 'en_US');
    }
}
