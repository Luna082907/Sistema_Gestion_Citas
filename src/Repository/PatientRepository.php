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
            'SELECT * FROM patients WHERE document_number LIKE :document_term OR first_name LIKE :first_name_term OR last_name LIKE :last_name_term ORDER BY last_name, first_name LIMIT 100'
        );
        $likeTerm = '%'.$term.'%';
        $statement->execute([
            'document_term' => $likeTerm,
            'first_name_term' => $likeTerm,
            'last_name_term' => $likeTerm,
        ]);
        return $statement->fetchAll();
    }

    public function findByDocument(string $document):array{
        $statement = $this->pdo->prepare(
            'SELECT * FROM patients WHERE document_number = :document LIMIT 1'
        );
        $statement->execute(['document' => $document]);
        $patient = $statement->fetch();
        return $patient === false?null : $patient;
    }

    public function create(array $data):int{
        $statement = $this->pdo->prepare(
            'INSERT INTO patients
            (document_type, document_number, first_name, last_name, birth_date, sex, phone, email)
            VALUES
            (:document_type, :document_number, :first_name, :last_name, :birth_date, :sex, :phone, :email)'
        );
        $statement->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function count():int{
        return (int) $this->pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn(); 
    }

    public function findById(int $id): ? array{
        $statement = $this->pdo->prepare(
            'SELECT * FROM patients WHERE id= :id LIMIT 1'
        );

        $statement->execute(['id' => $id]);
        $patient = $statement->fetch();
        return $patient === false ? null : $patient;
    }

    public function update(int $id, array $data): bool{
        $statement = $this->pdo->prepare(
            'UPDATE patients
                SET document_type = :document_type,
                document_number = :document_number,
                first_name = :first_name,
                last_name = :last_name,
                birth_date = :birth_date,
                sex = :sex,
                phone = :phone,
                email = :email
                WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'birth_date' => $data['birth_date'],
            'sex' => $data['sex'],
            'phone' => $data['phone'],
            'email' => $data['email'],
        ]);
    }

    public function delete(int $id): bool{
        $statement = $this->pdo->prepare(
            'DELETE FROM patients WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id
        ]);
    }
}

?>