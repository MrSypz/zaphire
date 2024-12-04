<?php

namespace Zernite\element;

use Zernite\util\Identifier;
use Zernite\util\ImplDatabase;

require_once __DIR__.'/../../../vendor/autoload.php';

class Team {
    protected ImplDatabase $db;
    protected Member $member;
    protected string $table_name = 'teams';

    public function __construct() {
        $this->db = Identifier::initDatabase();
        $this->member = new Member();
    }

    /**
     * Create a new team.
     *
     * @param array $data
     *
     * @return int|false TeamID on success, false on failure
     */
    public function createTeam(array $data): int|false {
        $teamId = $this->db->insertandId($this->table_name, $data);

        if ($teamId) {
            $memberAdded = $this->member->addMember([
                    'TeamID' => $teamId,
                    'UserID' => $data['OwnerID'],
                    'RoleID' => 1
            ]);

            if (!$memberAdded) {
                return false;
            }
        }

        return $teamId;
    }

    /**
     * Get team details by ID.
     *
     * @param int $teamId
     *
     * @return array|null
     */
    public function getTeamById(int $teamId): ?array {
        $sql = "SELECT * FROM $this->table_name WHERE TeamID = :teamId";

        return $this->db->fetchOne($sql, ['teamId' => $teamId]);
    }

    /**
     * Update team information.
     *
     * @param int   $teamId
     * @param array $data
     *
     * @return bool
     */
    public function updateTeam(int $teamId, array $data): bool {
        $condition = 'TeamID = :teamId';

        return $this->db->update($this->table_name, $data, $condition, ['teamId' => $teamId]);
    }

    /**
     * Delete a team and all associated members.
     *
     * @param int $teamId
     *
     * @return bool
     */
    public function deleteTeam(int $teamId): bool {
        if ( $this->teamExists($teamId)) {
            $this->member->removeAllMembers($teamId); // Remove all members from the team
            return $this->db->delete($this->table_name, 'TeamID = :teamId', ['teamId' => $teamId]);
        }
        return false;
    }

    /**
     * Check if a team exists.
     *
     * @param int $teamId
     *
     * @return bool
     */
    public function teamExists(int $teamId): bool {
        $sql = "SELECT COUNT(*) AS count FROM $this->table_name WHERE TeamID = :teamId";
        $result = $this->db->fetchOne($sql, ['teamId' => $teamId]);

        return (int) $result['count'] > 0;
    }

    public function getOwnerTeams(int $ownerId): ?array {
        $sql = "SELECT TeamID, Name, SponsorBanner, CreatedAt 
            FROM $this->table_name
            WHERE OwnerID = :ownerId";

        return $this->db->fetchAll($sql, ['ownerId' => $ownerId]);
    }

    public function inviteUser(int $userId) {

    }

    public function getOwnerName(int $teamId): ?array {
        $sql = "SELECT u.Username 
        FROM $this->table_name t
        JOIN users u ON t.OwnerID = u.UserID
        WHERE t.TeamID = :teamId
    ";
        $result = $this->db->fetchAll($sql, ['teamId' => $teamId]);

        return $result ? $result[0] : null;
    }
    /**
     * @return Member
     */
    public function getMember(): Member {
        return $this->member;
    }


}
