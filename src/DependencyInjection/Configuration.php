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

use Madcoders\SyliusRmaPlugin\Controller\RmaConfigurationController;
use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLog;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthorInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsent;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentTranslation;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentTranslationInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItemInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReason;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonTranslation;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonTranslationInterface;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfiguration;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfigurationInterface;
use Madcoders\SyliusRmaPlugin\Form\Type\OrderReturnConsentFormType;
use Madcoders\SyliusRmaPlugin\Form\Type\ReturnReasonFormType;
use Madcoders\SyliusRmaPlugin\Repository\OrderReturnRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Resource\Factory\Factory;
use Sylius\Component\Resource\Factory\TranslatableFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('madcoders_rma');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('return_form_pdf_enabled')
                    ->info('When false (default), the return-form PDF is not generated: the confirmation email is sent without it and the print/download endpoints and links are disabled. Enable to generate PDFs (requires wkhtmltopdf).')
                    ->defaultFalse()
                ->end()
                ->scalarNode('allow_unpaid_withdrawal')
                    ->info('When true (default), an unpaid not-yet-shipped order is offered the withdrawal flow: because it is not paid it is withdrawn instantly (the Sylius order is cancelled and the return resolves directly to the terminal "withdrawn" state with no admin step). When false, an unpaid order is not offered withdrawal at all. Paid orders are always withdrawable via admin approval regardless of this flag. Backed by the MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL env var; accepts a bool or an %env(bool:...)% placeholder (scalar rather than boolean node so env placeholders are allowed).')
                    ->defaultValue('%env(bool:MADCODERS_RMA_ALLOW_UNPAID_WITHDRAWAL)%')
                ->end()
                ->scalarNode('require_additional_information')
                    ->info('When false (default), the "Additional information" section (bank account number, account holder name and bank name / BIC-SWIFT) is not rendered on the customer return form and none of its fields are required. When true, the section is rendered and all three fields are required (the bank account number additionally keeps IBAN validation). Backed by the MADCODERS_RMA_REQUIRE_ADDITIONAL_INFORMATION env var; accepts a bool or an %env(bool:...)% placeholder (scalar rather than boolean node so env placeholders are allowed).')
                    ->defaultValue('%env(bool:MADCODERS_RMA_REQUIRE_ADDITIONAL_INFORMATION)%')
                ->end()
                ->scalarNode('limit_auth_attempts')
                    ->info('When true (default), the auth-code endpoints (code request and verification) are rate limited per client IP + order number to make brute forcing the emailed code infeasible; exceeding the limit returns HTTP 429 with a Retry-After header. Set to false to disable the built-in limiter (e.g. when the application is already fronted by its own rate limiter). Backed by the MADCODERS_RMA_LIMIT_AUTH_ATTEMPTS env var; accepts a bool or an %env(bool:...)% placeholder (scalar rather than boolean node so env placeholders are allowed).')
                    ->defaultValue('%env(bool:MADCODERS_RMA_LIMIT_AUTH_ATTEMPTS)%')
                ->end()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('authcode')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(AuthCode::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(AuthCodeInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturn::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(OrderReturnRepository::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return_item')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturnItem::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnItemInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return_change_log')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturnChangeLog::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnChangeLogInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return_reason')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturnReason::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnReasonInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(TranslatableFactory::class)->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(ReturnReasonFormType::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('translation')
                                ->addDefaultsIfNotSet()
                                    ->children()
                                    ->variableNode('options')->end()
                                    ->arrayNode('classes')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('model')->defaultValue(OrderReturnReasonTranslation::class)->cannotBeEmpty()->end()
                                            ->scalarNode('interface')->defaultValue(OrderReturnReasonTranslationInterface::class)->cannotBeEmpty()->end()
                                            ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                            ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                            ->scalarNode('repository')->cannotBeEmpty()->end()
                                        ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_return_consent')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                    ->scalarNode('model')->defaultValue(OrderReturnConsent::class)->cannotBeEmpty()->end()
                                    ->scalarNode('interface')->defaultValue(OrderReturnConsentInterface::class)->cannotBeEmpty()->end()
                                    ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                    ->scalarNode('factory')->defaultValue(TranslatableFactory::class)->end()
                                    ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->scalarNode('form')->defaultValue(OrderReturnConsentFormType::class)->cannotBeEmpty()->end()
                                ->end()
                                ->end()
                                ->arrayNode('translation')
                                ->addDefaultsIfNotSet()
                                    ->children()
                                    ->variableNode('options')->end()
                                    ->arrayNode('classes')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                        ->scalarNode('model')->defaultValue(OrderReturnConsentTranslation::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(OrderReturnConsentTranslationInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->end()
                                    ->end()
                                ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('madcoders_rma_order_return_change_log_author')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                    ->scalarNode('model')->defaultValue(OrderReturnChangeLogAuthor::class)->cannotBeEmpty()->end()
                                    ->scalarNode('interface')->defaultValue(OrderReturnChangeLogAuthorInterface::class)->cannotBeEmpty()->end()
                                    ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                    ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                    ->scalarNode('repository')->cannotBeEmpty()->end()
                                ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('madcoders_rma_configuration')
                            ->addDefaultsIfNotSet()
                                ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(RmaConfiguration::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(RmaConfigurationInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(RmaConfigurationController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                ->end()
                                ->end()
                            ->end()
                        ->end()

                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
