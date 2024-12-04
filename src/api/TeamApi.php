<?php

namespace Api;

use Zernite\element\Team;
use Zernite\element\User;
use Zernite\util\ResponseHandler;

class TeamApi extends BaseApi {
    private Team $team;
    private User $user;

    public function __construct() {
        parent::__construct();
        $this->team = new Team();
        $this->user = new User();
    }

    public function getOwnedTeams(): void {
        $this->validateRequiredFields(['ownerId']);

        $ownerId = $this->requestData['ownerId'];

        $data = $this->team->getOwnerTeams($ownerId);
        if ($data) {
            ResponseHandler::sendSuccess('Teams fetched successfully.', $data);
        } else {
            ResponseHandler::sendError('No teams found for this user.');
        }
    }

    public function getOwnerName(): void {
        $this->validateRequiredFields(['teamId']);

        $teamId = $this->requestData['teamId'];

        $data = $this->team->getOwnerName($teamId);
        if ($data)
            ResponseHandler::sendSuccess('Teams fetched successfully.', $data);
        else
            ResponseHandler::sendError('No teams found for this user.');
    }

    public function createTeam(): void {
        $this->validateRequiredFields(['teamName', 'OwnerID']);

        $teamData = [
                'Name' => $this->requestData['teamName'],
                'OwnerID' => $this->requestData['OwnerID'],
                'SponsorBanner' => $this->requestData['sponsorBanner'] ?? null,
        ];

        $currentUserId = $this->user->getUserID();
        if ((int) $teamData['OwnerID'] !== $currentUserId) {
            ResponseHandler::sendError('Unauthorized action: OwnerID mismatch.');
            return;
        }

        if ($this->team->createTeam($teamData)) {
            ResponseHandler::sendSuccess('Team created successfully.');
        } else {
            ResponseHandler::sendError('Failed to create team. Please try again.');
        }
    }
    public function deleteTeam(): void {
        $this->validateRequiredFields(['teamId']);

        $teamId = $this->requestData['teamId'];

        if ($this->team->deleteTeam($teamId)) {
            ResponseHandler::sendSuccess('Team deleted successfully.');
        } else {
            ResponseHandler::sendError('Failed to delete team. Please try again.');
        }
    }

    public function addMember(): void {
        $this->validateRequiredFields(['teamId', 'userId']);

        $teamId = $this->requestData['teamId'];
        $userId = $this->requestData['userId'];

        $memberData = [
            'TeamID' => $teamId,
            'UserID' => $userId,
            'RoleID' => $this->requestData['roleId'],
            'PhoneNumber' => $this->requestData['phoneNumber']
        ];

        $existingMember = $this->team->getMember()->getMemberByTeamAndUser($teamId, $userId);

        if ($existingMember) {
            ResponseHandler::sendError('Member already exists in the team.');
        } else {
            // Add the new member
            if ($this->team->getMember()->addMember($memberData)) {
                ResponseHandler::sendSuccess('Member added successfully.');
            } else {
                ResponseHandler::sendError('Failed to add member. Please try again.');
            }
        }
    }
    public function editMember(): void {
        $this->validateRequiredFields(['teamId', 'userId']);

        $teamId = $this->requestData['teamId'];
        $userId = $this->requestData['userId'];

        $memberData = [
            'RoleID' => $this->requestData['roleId'],
            'PhoneNumber' => $this->requestData['phoneNumber']
        ];

        $existingMember = $this->team->getMember()->getMemberByTeamAndUser($teamId, $userId);

        if ($existingMember) {
            if ($this->team->getMember()->updateMember($teamId, $userId, $memberData)) {
                ResponseHandler::sendSuccess('Member updated successfully.');
            } else {
                ResponseHandler::sendError('Failed to update member. Please try again.');
            }
        } else {
            ResponseHandler::sendError('Member does not exist in the team.');
        }
    }


    public function removeMember(): void {
        $this->validateRequiredFields(['teamId', 'userId']);

        if ($this->team->getMember()->removeMember($this->requestData['teamId'], $this->requestData['userId']))
            ResponseHandler::sendSuccess('Member removed successfully.');
        else
            ResponseHandler::sendError('Failed to remove member. Please try again.');
    }

    public function getMemberByTeamID(): void {
        $this->validateRequiredFields(['teamId']);

        $teamId = $this->requestData['teamId'];
        $teamExists = $this->team->getTeamById($teamId);

        if (!$teamExists) {
            ResponseHandler::sendError('Team not found.');
            return;
        }

        $data = $this->team->getMember()->getTeamMembersByIdAndName($teamId);
        if ($data) {
            ResponseHandler::sendSuccess('Team fetched successfully.', $data);
        } else {
            ResponseHandler::sendSuccess('Team found, but no members yet.', []);
        }
    }
    public function getMemberByTeamAndUserID(): void {
        $this->validateRequiredFields(['userId', 'teamId']);

        $teamId = $this->requestData['teamId'];
        $userId = $this->requestData['userId'];
        $teamExists = $this->team->getTeamById($teamId);

        if (!$teamExists) {
            ResponseHandler::sendError('Team not found.');
            return;
        }

        $data = $this->team->getMember()->getMemberByTeamAndUserID($teamId, $userId);
        if ($data) {
            ResponseHandler::sendSuccess('Team fetched successfully.', $data);
        } else {
            ResponseHandler::sendSuccess('Team found, but no members yet.', []);
        }
    }
}