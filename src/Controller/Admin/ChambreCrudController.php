<?php

namespace App\Controller\Admin;
use DateTime;
use App\Entity\Chambre;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ChambreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Chambre::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $imageField = ImageField::new('photo')
            ->setBasePath('uploads/photos')
            ->setUploadDir('public/uploads/photos')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]');

        if ($pageName === Crud::PAGE_NEW) {
            $imageField = $imageField->setFormTypeOptions(['required' => true]);
        } else {
            $imageField = $imageField->setFormTypeOptions(['required' => false]);
        }

        $prixJournalierField = MoneyField::new('prix_journalier');
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $prixJournalierField = $prixJournalierField->setFormTypeOptions([
                'constraints' => [
                    new GreaterThanOrEqual(['value' => 0, 'message' => 'Le prix journalier ne peut pas être négatif.'])
                ],
            ]);
        }

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('titre'),
            TextField::new('description_courte'),
            TextEditorField::new('description_longue')->onlyOnForms(),
            $imageField,
            $prixJournalierField->setCurrency('EUR')->setNumDecimals(0),
            DateTimeField::new('date_enregistrement')->setFormat('d/M/Y à H:m:s')->onlyOnIndex()->hideOnForm(),
        ];
    }

    public function createEntity(string $entityFqcn)
    {
        $chambre = new $entityFqcn;
        $chambre->setDateEnregistrement(new DateTime);
        return $chambre;
    }
}
