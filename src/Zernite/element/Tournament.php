<?php

namespace Zernite\element;

use Zernite\util\Identifier;
use Zernite\util\ImplDatabase;

class Tournament {
    protected ImplDatabase $db;
    protected string $table_name = 'tournaments';

    public function __construct() {
        $this->db = Identifier::initDatabase();
    }

    /**
     * Create a new tournament.
     *
     * @param array $data
     *
     * @return int|false TournamentID on success, false on failure
     */
    public function createTournament(array $data): int|false {
        return $this->db->insertAndId($this->table_name, $data);
    }

    /**
     * Get a tournament by its ID.
     *
     * @param int $tournamentId
     *
     * @return array|null
     */
    public function getTournamentById(int $tournamentId): ?array {
        $sql = "SELECT * FROM $this->table_name WHERE TournamentID = :tournamentId";
        return $this->db->fetchOne($sql, ['tournamentId' => $tournamentId]);
    }

    /**
     * Update a tournament's details.
     *
     * @param int   $tournamentId
     * @param array $data
     *
     * @return bool
     */
    public function updateTournament(int $tournamentId, array $data): bool {
        $condition = 'TournamentID = :tournamentId';
        return $this->db->update($this->table_name, $data, $condition, ['tournamentId' => $tournamentId]);
    }

    /**
     * Delete a tournament by ID.
     *
     * @param int $tournamentId
     *
     * @return bool
     */
    public function deleteTournament(int $tournamentId): bool {
        return $this->db->delete($this->table_name, 'TournamentID = :tournamentId', ['tournamentId' => $tournamentId]);
    }

    /**
     * Get all teams in a tournament.
     *
     * @param int $tournamentId
     *
     * @return array|null
     */
    public function getTeamsByTournament(int $tournamentId): ?array {
        $sql = "
            SELECT t.TeamID, t.Name 
            FROM tournamentteams tt
            JOIN teams t ON tt.TeamID = t.TeamID
            WHERE tt.TournamentID = :tournamentId
        ";
        return $this->db->fetchAll($sql, ['tournamentId' => $tournamentId]);
    }

    /**
     * Check if a tournament exists.
     *
     * @param int $tournamentId
     *
     * @return bool
     */
    public function tournamentExists(int $tournamentId): bool {
        $sql = "SELECT COUNT(*) AS count FROM $this->table_name WHERE TournamentID = :tournamentId";
        $result = $this->db->fetchOne($sql, ['tournamentId' => $tournamentId]);
        return (int) $result['count'] > 0;
    }

    /**
     * Get the status of a tournament by ID.
     *
     * @param int $tournamentId
     *
     * @return string|null
     */
    public function getTournamentStatus(int $tournamentId): ?string {
        $sql = "
            SELECT ts.StatusName 
            FROM tournaments t
            JOIN tournamentstatuses ts ON t.StatusID = ts.StatusID
            WHERE t.TournamentID = :tournamentId
        ";
        $result = $this->db->fetchOne($sql, ['tournamentId' => $tournamentId]);
        return $result['StatusName'] ?? null;
    }

    /**
     * Add a team to a tournament.
     *
     * @param int $tournamentId
     * @param int $teamId
     *
     * @return bool|string True if added successfully, or an error message.
     */
    public function addTeamToTournament(int $tournamentId, int $teamId): bool|string {
        // Check the tournament status
        $status = $this->getTournamentStatus($tournamentId);
        if ($status === 'Closed') {
            return "Cannot add teams. The tournament is closed for registration.";
        }

        // Add the team to the tournament
        $sql = "INSERT INTO tournamentteams (TournamentID, TeamID) VALUES (:tournamentId, :teamId)";
        return $this->db->execute($sql, [
                'tournamentId' => $tournamentId,
                'teamId' => $teamId
        ]);
    }
}