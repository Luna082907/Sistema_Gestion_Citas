<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class PatientRepository{
    public function __construct(private PDO $pdo){
    }

    public function search(string $term, int $page = 1, int $perPage = 10): array
{
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $where = '';
    $params = [];

    if ($term !== '') {
        $where = 'WHERE document_number LIKE :document_term'
            . ' OR first_name LIKE :first_term'
            . ' OR last_name LIKE :last_term';

        $like = '%' . $term . '%';
        $params = [
            'document_term' => $like,
            'first_term' => $like,
            'last_term' => $like,
        ];
    }

    $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM patients {$where}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $sql = "SELECT * FROM patients {$where}"
        . ' ORDER BY last_name, first_name'
        . ' LIMIT :limit OFFSET :offset';

    $stmt = $this->pdo->prepare($sql);

    foreach ($params as $name => $value) {
        $stmt->bindValue(':' . $name, $value, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
    ];
}

    public function findByDocument(string $document):array{
        $statement = $this->pdo->prepare(
            'SELECT * FROM patients WHERE document_number = :document LIMIT 1'
        );
        $statement->execute(['document' => $document]);
        $patient = $statement->fetch();
        return $patient === false?null : $patient;
    }

    public function findByPhone(string $phone):array{
        $statement = $this->pdo->prepare(
            'SELECT * FROM patients WHERE phone = :phone LIMIT 1'
        );
        $statement->execute(['phone' => $phone]);
        $patient = $statement->fetch();
        return $patient === false?null : $patient;
    }

    public function findByStatus(string $status):array{
        $statement = $this->pdo->prepare(
            'SELECT * FROM patients WHERE active = :status LIMIT 1'
        );
        $statement->execute(['status' => $status]);
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