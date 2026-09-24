<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class DoctorRepository{
    public function __construct(private PDO $pdo){
    }

    public function search(string $term, int $page = 1, int $perPage = 5): array
{
    $page = max(1, $page);
    $perPage = max(1, $perPage);
    $offset = ($page - 1) * $perPage;

    $where = '';
    $params = [];

    if ($term !== '') {
        $where = 'WHERE license_number LIKE :license_term'
            . ' OR first_name LIKE :first_term'
            . ' OR last_name LIKE :last_term';

        $like = '%' . $term . '%';
        $params = [
            'license_term' => $like,
            'first_term' => $like,
            'last_term' => $like,
        ];
    }

    $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM doctors {$where}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $sql = "SELECT * FROM doctors {$where}"
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

    public function active():array{
        return $this->pdo->query(
            'SELECT id, license_number, first_name, last_name, specialty, active FROM doctors WHERE active=1 ORDER BY first_name, last_name'
        )->fetchAll();
    }

    public function findActive(int $id):?array{
        $statement = $this->pdo->prepare(
            'SELECT id, license_number, first_name, last_name, specialty, active FROM doctors WHERE id=:id AND active = 1 LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $doctor = $statement->fetch();
        return $doctor === false ? null : $doctor;
    }

    public function create(array $data):int{
        $statement = $this->pdo->prepare(
            'INSERT INTO doctors
            (license_number, first_name, last_name, specialty, active)
            VALUES
            (:license_number, :first_name, :last_name, :specialty, :active)'
        );
        $statement->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ? array{
        $statement = $this->pdo->prepare(
            'SELECT * FROM doctors WHERE id= :id LIMIT 1'
        );

        $statement->execute(['id' => $id]);
        $doctor = $statement->fetch();
        return $doctor === false ? null : $doctor;
    }

    public function update(int $id, array $data): bool{
        $statement = $this->pdo->prepare(
            'UPDATE doctors
                SET license_number = :license_number,
                first_name = :first_name,
                last_name = :last_name,
                specialty = :specialty,
                active = :active
                WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'license_number' => $data['license_number'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'specialty' => $data['specialty'],
            'active' => $data['active'],
        ]);
    }

    public function delete(int $id): bool{
        $statement = $this->pdo->prepare(
            'DELETE FROM doctors WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id
        ]);
    }

}
