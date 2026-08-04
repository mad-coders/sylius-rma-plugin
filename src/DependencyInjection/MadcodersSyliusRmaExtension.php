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

        // Default for the env var backing gotenberg_url, so the plugin works out of the box
        // against a Gotenberg instance started via docker-compose.yml (default port 3000).
        $container->setParameter('env(GOTENBERG_URL)', 'http://127.0.0.1:3000');
        // No (string) cast here for the same reason as the flags below: the value may be an env
        // placeholder resolved only at runtime, so it stays a scalar at compile time.
        $gotenbergUrl = $config['gotenberg_url'];
        Assert::scalar($gotenbergUrl);
        $container->setParameter('madcoders_rma.gotenberg_url', $gotenbergUrl);

        // Default for the env var backing allow_unpaid_withdrawal, so the behaviour is unchanged
        // (auto-cancel enabled) when MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL is not defined.
        $container->setParameter('env(MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL)', 'true');
        // No (bool) cast here: the value may be an env placeholder (e.g. %env(bool:...)%) that is
        // only resolved at runtime; casting it at compile time would collapse it to true. It is
        // always a bool (plain config) or a string (env placeholder), i.e. a scalar.
        $allowUnpaidWithdrawal = $config['allow_unpaid_withdrawal'];
        Assert::scalar($allowUnpaidWithdrawal);
        $container->setParameter('madcoders_rma.allow_unpaid_withdrawal', $allowUnpaidWithdrawal);

        // Default for the env var backing require_additional_information, so the "Additional
        // information" section stays hidden and optional unless a merchant explicitly opts in.
        $container->setParameter('env(MADCODERS_RMA_REQUIRE_ADDITIONAL_INFORMATION)', 'false');
        // No (bool) cast here for the same reason as allow_unpaid_withdrawal above: the value may
        // be an env placeholder resolved only at runtime, so it stays a scalar at compile time.
        $requireAdditionalInformation = $config['require_additional_information'];
        Assert::scalar($requireAdditionalInformation);
        $container->setParameter('madcoders_rma.require_additional_information', $requireAdditionalInformation);

        // Default for the env var backing limit_auth_attempts, so the auth-code rate limiter is on
        // unless a merchant explicitly opts out.
        $container->setParameter('env(MADCODERS_RMA_LIMIT_AUTH_ATTEMPTS)', 'true');
        // No (bool) cast here for the same reason as the flags above: the value may be an env
        // placeholder resolved only at runtime, so it stays a scalar at compile time.
        $limitAuthAttempts = $config['limit_auth_attempts'];
        Assert::scalar($limitAuthAttempts);
        $container->setParameter('madcoders_rma.limit_auth_attempts', $limitAuthAttempts);

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
