<?php

namespace Zernite\element;

use Zernite\util\Identifier;
use Zernite\util\ImplDatabase;
use Zernite\element\Invite;

require_once __DIR__ . '/../../../vendor/autoload.php';

class Member {
    protected ImplDatabase $db;
    protected Invite $invite;
    protected string $table_name = 'teammembers';

    public function __construct() {
        $this->db = Identifier::initDatabase();
    }

    /**
     * Add a member to a team.
     *
     * @param array $data
     * @return bool
     */
    public function addMember(array $data): bool {
        return $this->db->insert($this->table_name, $data);
    }
    /**
     * Check if a member exists in a team.
     *
     * @param int $teamId
     * @param int|null $userId
     * @return bool
     */
    public function isMember(int $teamId, ?int $userId): bool {
        $sql = "SELECT COUNT(*) AS count FROM $this->table_name WHERE TeamID = :teamId AND UserID = :userId";
        $result = $this->db->fetchOne($sql, ['teamId' => $teamId, 'userId' => $userId]);
        return (int) $result['count'] > 0;
    }

    /**
     * Get all members of a team.
     *
     * @param int $teamId
     * @return array
     */
    public function getTeamMembersById(int $teamId): array {
        $sql = "SELECT * FROM $this->table_name WHERE TeamID = :teamId";
        return $this->db->fetchAll($sql, ['teamId' => $teamId]);
    }
    public function getMemberByTeamAndUser(int $teamId, int $userId): ?array {
        $sql = "SELECT * FROM $this->table_name WHERE TeamID = :teamId AND UserID = :userId";
        return $this->db->fetchOne($sql, ['teamId' => $teamId, 'userId' => $userId]);
    }
    

    /**
     * Get all members of a team and attribute from table users.
     *
     * @param int $teamId
     * @return array
     */
    public function getTeamMembersByIdAndName(int $teamId): array {
        $sql = "
        SELECT 
            tm.TeamID, 
            tm.UserID, 
            u.Username AS MemberUsername, 
            u.ProfilePicture AS MemberProfilePicture,
            tm.RoleID, 
            tr.Name AS RoleName, 
            tm.PhoneNumber, 
            tm.AddedAt
        FROM $this->table_name AS tm
        LEFT JOIN users AS u ON tm.UserID = u.UserID
        LEFT JOIN teamroles AS tr ON tm.RoleID = tr.RoleID
        WHERE tm.TeamID = :teamId
    ";
        return $this->db->fetchAll($sql, ['teamId' => $teamId]);
    }
    public function getMemberByTeamAndUserID(int $teamId, int $userId): ?array {
        $sql = "
        SELECT 
            tm.TeamID, 
            tm.UserID, 
            u.Username AS MemberUsername, 
            tm.RoleID,
            tr.Name AS RoleName, 
            tm.PhoneNumber, 
            tm.AddedAt
        FROM $this->table_name AS tm
        LEFT JOIN users AS u ON tm.UserID = u.UserID
        LEFT JOIN teamroles AS tr ON tm.RoleID = tr.RoleID
        WHERE tm.TeamID = :teamId AND tm.UserID = :userId
        ";
        return $this->db->fetchOne($sql, ['teamId' => $teamId, 'userId' => $userId]);
    }


    /**
     * Update a member's role or information in a team.
     *
     * @param int $teamId
     * @param int|null $userId
     * @param array $data
     * @return bool
     */
    public function updateMember(int $teamId, ?int $userId, array $data): bool {
        $condition = 'TeamID = :teamId AND UserID = :userId';
        return $this->db->update($this->table_name, $data, $condition, ['teamId' => $teamId, 'userId' => $userId]);
    }

    /**
     * Remove a member from a team.
     *
     * @param int $teamId
     * @param int|null $userId
     * @return bool
     */
    public function removeMember(int $teamId, ?int $userId): bool {
        $condition = 'TeamID = :teamId AND UserID = :userId';
        return $this->db->delete($this->table_name, $condition, ['teamId' => $teamId, 'userId' => $userId]);
    }

    public function removeAllMembers(int $teamId): bool {
        $condition = 'TeamID = :teamId';
        return $this->db->delete($this->table_name, $condition, ['teamId' => $teamId]);
    }
}
