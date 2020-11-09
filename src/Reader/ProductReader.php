<?php

namespace App\Reader;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManager;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReader;



class ProductReader
{
    /**
     * This is the entity manager
     */
    protected $entityManager;

    /**
     * 
     */
    protected $path;

    public function __construct(string $path, EntityManager $entityManager)
    {
        $this->path = $path;
        $this->entityManager = $entityManager;
    }

    protected function getSpreadsheet()
    {
        $inputFileName = $this->path;

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);

        return $spreadsheet;
    }

    public function execute()
    {

        $this->entityManager->beginTransaction();

        try {
            $this->initDb();


            $spreadsheet = $this->getSpreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();
            $rowIndex = 0;
            $products = [];

            $columNames = [];

            foreach ($worksheet->getRowIterator() as $row) {

                if ($rowIndex === 0) {
                    $columNames = $this->getColumNames($row);
                    $rowIndex++;
                    continue;
                }

                $productData = $this->getProductData($row,$columNames);

                $categoryRepository = $this->entityManager->getRepository(Category::class);
                $category = $categoryRepository->findOneByName($productData["categorie"]);
                if (!$category instanceof Category) {
                    $category = new Category();
                    $category->setName($productData["categorie"]);

                    $this->entityManager->persist($category);
                    $this->entityManager->flush();
                }

                $p = new Product();
                $p->setProductName($productData["naam"]);
                $p->setCategory($category);
                $p->setPrice($productData["prijs"]);

                $this->entityManager->persist($p);
                $this->entityManager->flush();

                $rowIndex++;
            }



            $this->entityManager->commit();

           return true;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            echo $e->getMessage();
            return false;
        }
    }
    public function initDb()
    {
        $cmd = $this->entityManager->getClassMetadata(Product::class);
        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');

        $cmd = $this->entityManager->getClassMetadata(Category::class);
        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }


    public function getColumNames($row)

    {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        foreach ($cellIterator as $cell) {
            $columNames[] = trim(strtolower($cell->getValue()));
        }
        return $columNames;
    }

    public function getProductData($row,$columNames)

    {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        $productdata = [];
        $i = 0;

        //  Loop through cells to collect productdata
        foreach ($cellIterator as $cell) {
            $currentColumnName = $columNames[$i];
            $productdata[$currentColumnName] = $cell->getvalue();
            $i++;
        }
        return $productdata;
    }

    public function getProductQuantity()
    {
        $highestRow = $this->getSpreadsheet()->getActiveSheet()->getHighestRow();
        $productQuantity = $highestRow - 1 ;
        return $productQuantity;
    }
}
