<?php

namespace Zernite\element;

use Zernite\util\Identifier;
use Zernite\util\ImplDatabase;

class MatchGame {
    protected ImplDatabase $db;
    protected string $table_name = 'matches';

    public function __construct() {
        $this->db = Identifier::initDatabase();
    }

    /**
     * Create a new match.
     *
     * @param array $data
     *
     * @return int|false MatchID on success, false on failure
     */
    public function createMatch(array $data): int|false {
        return $this->db->insertAndId($this->table_name, $data);
    }

    /**
     * Get match details by ID.
     *
     * @param int $matchId
     *
     * @return array|null
     */
    public function getMatchById(int $matchId): ?array {
        $sql = "SELECT * FROM $this->table_name WHERE MatchID = :matchId";
        return $this->db->fetchOne($sql, ['matchId' => $matchId]);
    }

    /**
     * Update match details.
     *
     * @param int   $matchId
     * @param array $data
     *
     * @return bool
     */
    public function updateMatch(int $matchId, array $data): bool {
        $condition = 'MatchID = :matchId';
        return $this->db->update($this->table_name, $data, $condition, ['matchId' => $matchId]);
    }

    /**
     * Delete a match by ID.
     *
     * @param int $matchId
     *
     * @return bool
     */
    public function deleteMatch(int $matchId): bool {
        return $this->db->delete($this->table_name, 'MatchID = :matchId', ['matchId' => $matchId]);
    }

    /**
     * Get all matches for a tournament.
     *
     * @param int $tournamentId
     *
     * @return array|null
     */
    public function getMatchesByTournament(int $tournamentId): ?array {
        $sql = "SELECT * FROM $this->table_name WHERE TournamentID = :tournamentId";
        return $this->db->fetchAll($sql, ['tournamentId' => $tournamentId]);
    }

    /**
     * Record the outcome of a match.
     *
     * @param int $matchId
     * @param int $team1Score
     * @param int $team2Score
     * @param int $outcomeId
     *
     * @return bool
     */
    public function recordMatchOutcome(int $matchId, int $team1Score, int $team2Score, int $outcomeId): bool {
        $data = [
                'Team1Score' => $team1Score,
                'Team2Score' => $team2Score,
                'OutcomeID' => $outcomeId,
        ];
        return $this->updateMatch($matchId, $data);
    }
}
