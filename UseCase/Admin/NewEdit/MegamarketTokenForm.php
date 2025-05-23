<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Megamarket\UseCase\Admin\NewEdit;

use BaksDev\Users\Profile\UserProfile\Repository\UserProfileChoice\UserProfileChoiceInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MegamarketTokenForm extends AbstractType
{
    private UserProfileChoiceInterface $profileChoice;

    public function __construct(UserProfileChoiceInterface $profileChoice)
    {
        $this->profileChoice = $profileChoice;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var MegamarketTokenDTO $data */
        $data = $builder->getData();

        if(!$data->getProfile())
        {
            /* TextType */
            $builder->add('profile', ChoiceType::class, [
                'choices' => $this->profileChoice->getActiveUserProfile(),
                'choice_value' => function(?UserProfileUid $profile) {
                    return $profile?->getValue();
                },
                'choice_label' => function(UserProfileUid $profile) {
                    return $profile->getAttr();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'attr' => ['data-select' => 'select2',]
            ]);
        }

        $builder->add('token', TextareaType::class, ['required' => false]);

        $builder->add('company', NumberType::class); //153373

        $builder->add('percent', IntegerType::class, [
            'attr' => ['max' => 100, 'min' => -100]
        ]);

        $builder->add('rate', IntegerType::class);

        $builder->add('active', CheckboxType::class, ['required' => false]);


        /* Сохранить ******************************************************/
        $builder->add(
            'megamarket_token',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MegamarketTokenDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}
