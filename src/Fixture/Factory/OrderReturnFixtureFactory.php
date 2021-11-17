<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Fixture\Factory;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderReturnFixtureFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{
    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var OptionsResolver */
    private $optionsResolver;

    /** @var \Faker\Generator */
    private $faker;

    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;

        $this->faker = \Faker\Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = []): OrderReturnInterface
    {
        $options = $this->optionsResolver->resolve($options);

        /** @var ChannelInterface|null $channel */
        $channel = $this->channelRepository->findOneByCode($options['channel_code']);
        if ($channel === null) {
            throw new ChannelNotFoundException(sprintf('Channel %s has not been found, please create it before adding this fixture !', $options['channel_code']));
        }

        $orderReturn = new OrderReturn();
        $orderReturn->setOrderNumber($options['order_number']);
        $orderReturn->setReturnNumber($options['return_number']);
        $orderReturn->setChannelCode($options['channel_code']);
        $orderReturn->setReturnReason($options['return_reason']);
        $orderReturn->setOrderReturnConsents($options['return_consents']);
        $orderReturn->setCity($options['city']);
        $orderReturn->setPostcode($options['postcode']);
        $orderReturn->setStreet($options['street']);
        $orderReturn->setPhoneNumber($options['phone_number']);
        $orderReturn->setCustomerIp($options['customer_ip']);
        $orderReturn->setCustomerNumber($options['customer_number']);

        return $orderReturn;
    }

    /**
     * {@inheritdoc}
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

            ->setDefault('customer_ip', function (Options $options): string {
                return (string) $this->faker->ipv4;
            })
            ->setAllowedTypes('customer_ip', 'string')

            ->setDefault('city', function (Options $options): string {
                return $this->faker->city;
            })
            ->setAllowedTypes('city', 'string')

            ->setDefault('postcode', function (Options $options): string {
                return (string) $this->faker->postcode;
            })
            ->setAllowedTypes('postcode', 'string')

            ->setDefault('street', function (Options $options): string {
                return (string) $this->faker->postcode;
            })
            ->setAllowedTypes('street', 'string')

            ->setDefault('phone_number', function (Options $options): string {
                return (string) $this->faker->phoneNumber;
            })
            ->setAllowedTypes('phone_number', 'string')

            ->setRequired('customer_number')
            ->setAllowedTypes('customer_number', 'integer')
        ;
    }
}
