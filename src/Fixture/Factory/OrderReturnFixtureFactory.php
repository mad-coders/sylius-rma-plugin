<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Fixture\Factory;

use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderReturnFixtureFactory extends AbstractExampleFactory implements ExampleFactoryInterface
{
    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var OptionsResolver */
    private $optionsResolver;

    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;

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
        ;
    }
}
