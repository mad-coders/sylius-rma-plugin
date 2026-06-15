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

namespace Madcoders\SyliusRmaPlugin\DependencyInjection;

use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Webmozart\Assert\Assert;

final class MadcodersSyliusRmaExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        Assert::isArray($config['resources']);
        $this->registerResources('madcoders_rma', 'doctrine/orm', $config['resources'], $container);

        $container->setParameter('madcoders_rma.return_form_pdf_enabled', (bool) $config['return_form_pdf_enabled']);

        // Default for the env var backing allow_unpaid_withdrawal, so the behaviour is unchanged
        // (auto-cancel enabled) when MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL is not defined.
        $container->setParameter('env(MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL)', 'true');
        // No (bool) cast here: the value may be an env placeholder (e.g. %env(bool:...)%) that is
        // only resolved at runtime; casting it at compile time would collapse it to true. It is
        // always a bool (plain config) or a string (env placeholder), i.e. a scalar.
        $allowUnpaidWithdrawal = $config['allow_unpaid_withdrawal'];
        Assert::scalar($allowUnpaidWithdrawal);
        $container->setParameter('madcoders_rma.allow_unpaid_withdrawal', $allowUnpaidWithdrawal);

        $loader->load('services.xml');
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineMigrations($container);
    }

    protected function getMigrationsNamespace(): string
    {
        return 'Madcoders\SyliusRmaPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@MadcodersSyliusRmaPlugin/Migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return ['Sylius\Bundle\CoreBundle\Migrations'];
    }
}
