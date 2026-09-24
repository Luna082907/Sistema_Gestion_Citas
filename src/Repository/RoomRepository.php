<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class RoomRepository{
    public function __construct(private PDO $pdo){
    }

    public function active():array{
        return $this->pdo->query(
            'SELECT id, code, name FROM rooms WHERE active = 1 ORDER BY code'
        )->fetchAll();
    }

    public function findActive(int $id):?array{
        $statement = $this->pdo->prepare(
            'SELECT id, code, name FROM rooms WHERE id = :id AND active = 1 LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $room = $statement->fetch();
        return $room === false ? null: $room;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO rooms (code, name, active) VALUES (:code, :name, :active)'
        );
        $statement->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM rooms WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $room = $statement->fetch();
        return $room === false ? null : $room;
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE rooms SET code = :code, name = :name, active = :active WHERE id = :id'
        );
        return $statement->execute([
            'id' => $id,
            'code' => $data['code'],
            'name' => $data['name'],
            'active' => $data['active'],
        ]);
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

    $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM rooms {$where}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $sql = "SELECT * FROM rooms {$where}"
        . ' ORDER BY name'
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
}

?>