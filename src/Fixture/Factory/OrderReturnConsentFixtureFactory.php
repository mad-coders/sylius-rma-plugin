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

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsent;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderReturnConsentFixtureFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{
    /** @var OptionsResolver */
    private $optionsResolver;

    /** @var \Faker\Generator */
    private $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = []): OrderReturnConsentInterface
    {
        $options = $this->optionsResolver->resolve($options);

        $orderReturnConsent = new OrderReturnConsent();
        $orderReturnConsent->setCurrentLocale($options['current_locale']);
        $orderReturnConsent->setEnabled($options['enabled']);
        $orderReturnConsent->setCode($options['code']);
        $orderReturnConsent->setName($options['name']);
        $orderReturnConsent->setSlug($options['slug']);
        $orderReturnConsent->setDescription($options['description']);

        return $orderReturnConsent;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('enabled', true)
            ->setAllowedTypes('enabled', 'bool')
            ->setRequired('code')
            ->setAllowedTypes('code', 'string')
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
