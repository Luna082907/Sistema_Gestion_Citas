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
}

?>