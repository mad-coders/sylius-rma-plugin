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

namespace Madcoders\SyliusRmaPlugin\Fixture\Factory;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReason;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class OrderReturnReasonFixtureFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{
    private readonly OptionsResolver $optionsResolver;

    public function __construct()
    {
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /**
     * @inheritdoc
     */
    public function create(array $options = []): OrderReturnReasonInterface
    {
        $options = $this->optionsResolver->resolve($options);
        Assert::boolean($options['enabled']);
        Assert::string($options['code']);
        Assert::integer($options['deadline_to_return']);
        Assert::string($options['name']);
        Assert::string($options['slug']);
        Assert::nullOrString($options['description']);

        $orderReturnReason = new OrderReturnReason();
        $orderReturnReason->setCurrentLocale('en_US');
        $orderReturnReason->setEnabled($options['enabled']);
        $orderReturnReason->setCode($options['code']);
        $orderReturnReason->setDeadlineToReturn($options['deadline_to_return']);
        $orderReturnReason->setName($options['name']);
        $orderReturnReason->setSlug($options['slug']);
        $orderReturnReason->setDescription($options['description']);

        return $orderReturnReason;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('enabled', true)
            ->setAllowedTypes('enabled', 'bool')
            ->setRequired('code')
            ->setAllowedTypes('code', 'string')
            ->setRequired('deadline_to_return')
            ->setAllowedTypes('deadline_to_return', 'int')
            ->setRequired('name')
            ->setAllowedTypes('name', 'string')
            ->setRequired('slug')
            ->setAllowedTypes('slug', 'string')
            ->setDefault('description', null)
            ->setAllowedTypes('description', ['string', 'null'])
            ->setDefault('current_locale', 'en_US')
            ->setAllowedTypes('current_locale', 'string');
    }
}
