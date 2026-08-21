<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class PatientRepository{
    public function __construct(private PDO $pdo){
    }

    public function search(string $term = ''): array{
        if ($term === ''){
            return $this->pdo->query(
                'SELECT * FROM patients ORDER BY last_name, first_name LIMIT 100'
            )-> fetchAll();
        }

        $statement = $this->pdo->prepare(
            'SELECT * FROM patients WHERE document_number LIKE :term OR first_name LIKE :term OR last_name LIKE :term ORDER BY last_name, first_name LIMIT 100'
        );
        $statement->execute(['term' => '%'.$term.'%']);
        return $statement->fetchAll();
    }

    public function findByDocument(string $document):array{
        $statement = $this->pdo->prepare(
            'SELECT * FROM patients WHERE document_number = :document LIMIT 1'
        );
        $statement->execute(['document' => $docuemnt]);
        $patient = $statement->fetch();
        return $patient === false?null : $patient;
    }

    public function create(array $data):int{
        $statement = $this->pdo->prepare(
            'INSERT INTO patients (document_type, document_number, first_name, last_name, birth_date, :sex, :phone, :email)'
        );
        $statement->execuse($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function count():int{
        return (int) $this->pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn(); 
    }
}

?>