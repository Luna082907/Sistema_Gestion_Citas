<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class UserRepository{

    public function __construct(private PDO $pdo){
    }

    public function findActiveByEmail(string $email):?array{
        $statement = $this->pdo->prepare(
            'SELECT id, name, email, password_hash, role FROM users WHERE email = :email AND active = 1 LIMIT 1'
        );
        $statement->execute(['email => $email']);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }
}

?>