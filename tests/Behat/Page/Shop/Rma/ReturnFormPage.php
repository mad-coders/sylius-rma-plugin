<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Behaviour\SelectFormElement;
use  Behat\Mink\Exception\ElementNotFoundException;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\FlashNotificationInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\FlashNotificationTrait;

/**
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */
class ReturnFormPage extends SymfonyPage implements ReturnFormPageInterface, FlashNotificationInterface
{
    use FlashNotificationTrait;

    use SelectFormElement;

    /**
     * @throws ElementNotFoundException
     */
    public function selectReturnReason(string $value): void
    {
        if ($this->checkReturnFormHasReasonSelect()) {
            $this->getDocument()->selectFieldOption('madcoders_rma_return_item_returnReason', $value);
        }
    }

    /**
     * @throws ElementNotFoundException
     */
    public function fillBankAccountField(): void
    {
        if ($this->returnFormHasBankAccountField()) {
            $this->getDocument()->fillField(
                'madcoders_rma_return_item_bankAccountNumber',
                'PL39116000061780056464618314'
            );
        }
    }

    /**
     * @throws ElementNotFoundException
     */
    public function fillNoteField(string $noteText): void
    {
        if ($this->returnFormHasNoteField()) {
            $this->getDocument()->fillField(
                'madcoders_rma_return_item_customerNote',
                $noteText
            );
        }
    }

    /**
     * @throws ElementNotFoundException
     */
    public function submitThisOrderReturnForm(): void
    {
        $this->getElement('rma_submit_return_form')->click();
    }

    private function returnFormHasNoteField(): bool
    {
        return $this->getDocument()->hasField('madcoders_rma_return_item_customerNote');
    }

    private function returnFormHasBankAccountField(): bool
    {
        return $this->getDocument()->hasField('madcoders_rma_return_item_bankAccountNumber');
    }

    private function checkReturnFormHasReasonSelect(): bool
    {
       return $this->getDocument()->hasSelect('madcoders_rma_return_item_returnReason');
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'rma_submit_return_form' => '[data-test-madcoders-rma-submit-return-form-button]'
        ]);
    }

    public function getRouteName(): string
    {
        return 'madcoders_rma_return_form';
    }
}
