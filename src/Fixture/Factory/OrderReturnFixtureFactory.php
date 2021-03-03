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
        $orderReturn->setChannelCode($options['channel_code']);
        $orderReturn->setCustomerIp($options['customer_ip']);
        $orderReturn->setCity($options['city']);
        $orderReturn->setPostcode($options['postcode']);
        $orderReturn->setStreet($options['street']);
        $orderReturn->setPhoneNumber($options['phone_number']);

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

            ->setRequired('customer_ip')
            ->setAllowedTypes('customer_ip', 'string')
            ->setDefault('customer_ip', function (): string {
                return $this->faker->ipv4;
            })

            ->setRequired('city')
            ->setAllowedTypes('city', 'string|null')
            ->setDefault('city', function (): string {
                return $this->faker->city;
            })

            ->setRequired('postcode')
            ->setAllowedTypes('postcode', 'string')
            ->setDefault('postcode', function (): string {
                return $this->faker->postcode;
            })

            ->setRequired('street')
            ->setAllowedTypes('street', 'string')
            ->setDefault('street', function (): string {
                return $this->faker->postcode;
            })

            ->setRequired('phone_number')
            ->setAllowedTypes('phone_number', 'string')
            ->setDefault('phone_number', function (): string {
                return $this->faker->phoneNumber;
            })
        ;
    }
}
