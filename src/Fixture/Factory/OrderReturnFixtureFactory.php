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

use Faker\Factory;
use Faker\Generator;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class OrderReturnFixtureFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{
    private readonly OptionsResolver $optionsResolver;

    /** @var Generator */
    private $faker;

    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(private readonly ChannelRepositoryInterface $channelRepository)
    {
        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /**
     * @inheritdoc
     */
    public function create(array $options = []): OrderReturnInterface
    {
        $options = $this->optionsResolver->resolve($options);
        Assert::string($options['channel_code']);
        Assert::string($options['order_number']);
        Assert::string($options['return_number']);
        Assert::string($options['return_reason']);
        Assert::isArray($options['return_consents']);
        Assert::string($options['city']);
        Assert::string($options['postcode']);
        Assert::string($options['street']);
        Assert::string($options['phone_number']);
        Assert::string($options['customer_ip']);
        Assert::scalar($options['customer_number']);

        $channelCode = $options['channel_code'];

        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneByCode($channelCode);
        if ($channel === null) {
            throw new ChannelNotFoundException(sprintf('Channel %s has not been found, please create it before adding this fixture !', $channelCode));
        }

        $orderReturn = new OrderReturn();
        $orderReturn->setOrderNumber($options['order_number']);
        $orderReturn->setReturnNumber($options['return_number']);
        $orderReturn->setChannelCode($channelCode);
        $orderReturn->setReturnReason($options['return_reason']);
        $orderReturn->setOrderReturnConsents($options['return_consents']);
        $orderReturn->setCity($options['city']);
        $orderReturn->setPostcode($options['postcode']);
        $orderReturn->setStreet($options['street']);
        $orderReturn->setPhoneNumber($options['phone_number']);
        $orderReturn->setCustomerIp($options['customer_ip']);
        $orderReturn->setCustomerNumber((string) $options['customer_number']);

        return $orderReturn;
    }

    /**
     * @inheritdoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('channel_code')
            ->setAllowedTypes('channel_code', 'string')

            ->setRequired('order_number')
            ->setAllowedTypes('order_number', 'string')

            ->setRequired('return_number')
            ->setAllowedTypes('return_number', 'string')

            ->setDefault('return_consents', [])
            ->setAllowedTypes('return_consents', 'array')

            ->setRequired('return_reason')
            ->setAllowedTypes('return_reason', 'string')

            ->setDefault('customer_ip', fn (Options $options): string => $this->faker->ipv4)
            ->setAllowedTypes('customer_ip', 'string')

            ->setDefault('city', fn (Options $options): string => $this->faker->city)
            ->setAllowedTypes('city', 'string')

            ->setDefault('postcode', fn (Options $options): string => $this->faker->postcode)
            ->setAllowedTypes('postcode', 'string')

            ->setDefault('street', fn (Options $options): string => $this->faker->postcode)
            ->setAllowedTypes('street', 'string')

            ->setDefault('phone_number', fn (Options $options): string => $this->faker->phoneNumber)
            ->setAllowedTypes('phone_number', 'string')

            ->setRequired('customer_number')
            ->setAllowedTypes('customer_number', ['integer', 'string'])
        ;
    }
}
