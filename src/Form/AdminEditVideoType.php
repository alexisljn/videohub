<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Video;
use App\Manager\CategoryManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminEditVideoType extends AbstractType
{
   // On a besoin des categories pour construire ce formulaire, on instancie donc dans le constructeur un CategoryManager

    private $categoryManager;
    private $categories;

    public function __construct(CategoryManager $categoryManager)
    {
        $this->categoryManager = $categoryManager;
        $this->categories = $this->getCategories();
    }

    private function getCategories()
    {
        $categories = $this->categoryManager->getCategories();
        return $categories;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('createdAt', DateTimeType::class)
            ->add('url', UrlType::class)
            ->add('published')
            ->add('category', ChoiceType::class, [
                'placeholder' => '-',
                'choices' => $this->categories,
                'choice_label' => function(Category $category) {
                    return $category->getTitle();
                },
                'required' => false
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
