<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Form\Type;


use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ConfigChannelSelectFormType extends AbstractType
{
    /** @var RepositoryInterface */
    private $channelsRepository;

    /**
     * ConfigChannelSelectFormType constructor.
     * @param RepositoryInterface $channelsRepository
     */
    public function __construct(RepositoryInterface $channelsRepository)
    {
        $this->channelsRepository = $channelsRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = $this->channelsRepository->findAll();
        $choicesChannel = [];
        foreach ($choices as $choice) {
            $choicesChannel[$choice->getId()] = $choice->getName();
        }
        $builder->add('channelChoice', ChoiceType::class, [
            'label'    =>  false,
            'required' => true,
            'choices' => array_flip($choicesChannel),
            'constraints' => [
                new NotBlank([
                    'message' => 'madcoders_rma.validator.not_blank',
                ])
            ],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'madcoders_rma_admin_choice_channel';
    }
}
