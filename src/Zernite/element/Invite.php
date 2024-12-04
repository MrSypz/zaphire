<?php 

namespace Zernite\element;

use Zernite\util\Identifier;
use Zernite\util\ImplDatabase;

require_once __DIR__ . '/../../../vendor/autoload.php';


class Invite {
    protected ImplDatabase $db;
    protected string $table_name = 'teaminvite';

    public function __construct() {
        $this->db = Identifier::initDatabase();
    }
    public function invite(array $data): bool {
        return $this->db->insert($this->table_name, $data);
    }
    public function accept(int $teamId, int $userId): bool {
        $sql = "SELECT * FROM $this->table_name WHERE TeamID = :teamId AND UserID = :userId";
        $result = $this->db->fetchOne($sql, ['teamId' => $teamId, 'userId' => $userId]);
        return (int) $result['count'] > 0;
    }
}