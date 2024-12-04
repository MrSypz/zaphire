let teams = [];
let currentTeam = null;
let editingMemberId = null;

const addMemberBtn = document.querySelector('.add-member-btn');
const modal = document.getElementById('memberModal');
const memberForm = document.getElementById('memberForm');
const modalTitle = document.getElementById('modalTitle');
const submitBtn = document.getElementById('submitBtn');
const createTeamModal = document.getElementById('createTeamModal');
const createTeamForm = document.getElementById('createTeamForm');
const createTeamBtn = document.querySelector('.create-team-btn');

async function initializeTeams() {
    const Zzerdal = new Zerdal();
    try {
        const userResponse = await ApiUtil.callApi(Entity.USER, Actions[Entity.USER].GET_USER_ID);
        if (!userResponse.success) {
            throw new Error('Failed to get user data');
        }
        const ownerId = userResponse.data;

        const teamsResponse = await ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].GET_OWNED_TEAMS, {ownerId});
        if (!teamsResponse.success) {
            throw new Error('Failed to fetch teams');
        }

        teams = teamsResponse.data;

        if (teams.length === 0) {
            const tabsContainer = document.querySelector('.tabs');
            tabsContainer.innerHTML = '<p class="no-teams">No teams yet. Create your first team!</p>';
            document.querySelector('.team-table tbody').innerHTML = '';
            return;
        }

        currentTeam = teams[0].TeamID;
        await renderTabs();
        await renderTeamMembers(teams[0]);
    } catch (error) {
        console.error('Error initializing:', error);
        Zzerdal.show('Failed to initialize teams: ' + error.message, 'error');
    }
}

async function renderTabs() {
    const oldTabs = document.querySelector('.tabs');
    const newTabs = oldTabs.cloneNode(true);
    oldTabs.parentNode.replaceChild(newTabs, oldTabs);

    newTabs.innerHTML = teams.map((team, index) => `
        <button class="tab ${index === 0 ? 'active' : ''}" data-team-id="${team.TeamID}">
            ${team.Name}
        </button>
    `).join('');

    // Add single event listener for tabs
    newTabs.addEventListener('click', async (e) => {
        if (e.target.classList.contains('tab')) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            const teamId = e.target.dataset.teamId;
            currentTeam = teamId;

            // Find the complete team object
            const team = teams.find(t => t.TeamID.toString() === teamId.toString());
            if (team) {
                await renderTeamMembers(team);
            } else {
                const Zzerdal = new Zerdal();
                Zzerdal.show('Team not found', 'error');
            }
        }
    });
}

// Event Listeners
addMemberBtn.addEventListener('click', () => {
    editingMemberId = null;
    modalTitle.textContent = 'Add Team Member';
    submitBtn.textContent = 'Add Member';
    memberForm.reset();
    modal.classList.add('active');
});

memberForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const Zzerdal = new Zerdal();
    console.log('Current Team ID:', currentTeam);

    const memberData = {
        name: document.getElementById('userId').value,
        phoneNumber: document.getElementById('phoneNumber').value,
        role: document.getElementById('roleId').value
    };

    try {
        const team = teams.find(t => t.TeamID.toString() === currentTeam.toString());
        if (!team) {
            throw new Error('Current team not found');
        }

        if (editingMemberId) {
            const updateData = {
                teamId: team.TeamID,
                userId: editingMemberId,
                roleId: memberData.role,
                phoneNumber: memberData.phoneNumber
            };

            const updateResponse = await ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].EDIT_MEMBER, updateData);

            if (!updateResponse.success) {
                throw new Error(updateResponse.error || 'Failed to update member');
            }
            Zzerdal.show("Member updated successfully", "success");
        } else {
            const newMemberData = {
                teamId: team.TeamID, userId: memberData.name, // For new members
                roleId: memberData.role, phoneNumber: memberData.phoneNumber,
            };

            const addResponse = await ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].ADD_MEMBER, newMemberData);

            if (!addResponse.success) {
                throw new Error(addResponse.error || 'Failed to add member');
            }
            Zzerdal.show("Member added successfully", "success");
        }

        // Refresh the team members
        const refreshResponse = await ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].GET_MEMBERS_BY_TEAM_ID, {teamId: team.TeamID});

        if (refreshResponse.success) {
            team.members = refreshResponse.data;
            await renderTeamMembers(team);
            closeModal();
            memberForm.reset();
        } else {
            throw new Error('Failed to refresh team members');
        }
    } catch (error) {
        console.error('Error managing member:', error);
        Zzerdal.show(error.message || 'An error occurred while managing the team member', 'error');
    }
});

document.querySelector('.tabs').addEventListener('click', async (e) => {
    if (e.target.classList.contains('tab')) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        e.target.classList.add('active');
        currentTeam = e.target.dataset.teamId;
        const team = teams.find(t => t.TeamID === currentTeam);
        await renderTeamMembers(team);
    }
});

async function renderTeamMembers(team) {
    const tbody = document.querySelector('.team-table tbody');
    const Zzerdal = new Zerdal();
    try {
        if (!team) {
            throw new Error('Team object is required');
        }
        if (!team.TeamID) {
            throw new Error('Team ID is required');
        }

        // Fetch members and owner name in parallel
        const [membersResponse, ownerResponse] = await Promise.all([ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].GET_MEMBERS_BY_TEAM_ID, {teamId: team.TeamID.toString()}), ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].GET_OWNER_NAME, {teamId: team.TeamID.toString()})]);

        if (!membersResponse.success) {
            throw new Error(membersResponse.error || 'Failed to fetch team members');
        }

        if (!ownerResponse.success) {
            throw new Error(ownerResponse.error || 'Failed to fetch team owner');
        }

        // Update team header information
        const teamHeader = document.createElement('div');
        teamHeader.className = 'team-header';
        teamHeader.innerHTML = `
            <div class="team-info">
                <h2 class="team-name" id="team-name-placeholder">Current Team: ${team.Name}</h2>
                <p class="team-creator" id="team-createby-placeholder">Create by ${ownerResponse.data.Username}</p>
            </div>
        `;
        console.log(membersResponse.data.length > 0 ? membersResponse.data : 'No members in this team');
        // Update table content
        tbody.innerHTML = membersResponse.data.length > 0 ? membersResponse.data.map(member => `
                <tr>
                    <td>
                        <div class="member">
                            <div class="avatar ${getRoleClass(member.RoleName)}">
                                ${member.MemberProfilePicture ? `<img src="${member.MemberProfilePicture}" 
                                           alt="${member.MemberUsername}" 
                                           class="avatar-image"
                                           onError="this.onerror=null; this.src='/placeholder.svg?height=40&width=40'; this.alt='Default avatar';">` : getInitials(member.MemberUsername)}
                            </div>
                            <span class="member-name">${member.MemberUsername}</span>
                        </div>
                    </td>
                    <td>${member.PhoneNumber || '-'}</td>
                    <td>
                        <span class="badge ${getRoleBadgeClass(member.RoleName)}">
                            ${member.RoleName}
                        </span>
                    </td>
                    <td class="actions-cell">
                        <button class="action-btn" onclick="toggleDropdown(event)" aria-label="Member actions">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button class="dropdown-item" onclick="editMember('${member.UserID}', '${team.TeamID}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="dropdown-item text-red" onclick="deleteMember('${member.UserID}', '${team.TeamID}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('') : '<tr><td colspan="4" class="text-center">No members in this team</td></tr>';

        // Update the table container
        const tableContainer = document.querySelector('.team-table').parentElement;
        const existingHeader = tableContainer.querySelector('.team-header');
        if (existingHeader) {
            existingHeader.remove();
        }
        tableContainer.insertBefore(teamHeader, tableContainer.firstChild);

        // Add delete team button
        const deleteSection = document.createElement('div');
        deleteSection.className = 'delete-team-section';
        deleteSection.innerHTML = `
            <button class="delete-team-btn" onclick="deleteTeam('${team.TeamID}')">
                <i class="fas fa-trash"></i> Delete Team
            </button>
        `;

        const existingDeleteSection = document.querySelector('.delete-team-section');
        if (existingDeleteSection) {
            existingDeleteSection.remove();
        }
        tableContainer.appendChild(deleteSection);

    } catch (error) {
        console.error('Error rendering members:', error);
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Failed to load team members</td></tr>';
        Zzerdal.show('Failed to load team members: ' + error.message, 'error');
    }
}

function getRoleClass(role) {
    switch (role.toLowerCase()) {
        case 'captain':
            return 'role-captain';
        case 'manager':
            return 'role-manager';
        default:
            return 'role-member';
    }
}

function getRoleBadgeClass(role) {
    switch (role.toLowerCase()) {
        case 'captain':
            return 'badge-captain';
        case 'manager':
            return 'badge-manager';
        default:
            return 'badge-member';
    }
}

async function deleteTeam(teamId) {
    try {
        const response = await ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].DELETE_TEAM, {teamId});

        if (response.success) {
            const Zzerdal = new Zerdal();
            Zzerdal.show('Team deleted successfully', 'success');
            await initializeTeams(); // Refresh the teams list
        } else {
            throw new Error(response.message || 'Failed to delete team');
        }
    } catch (error) {
        console.error('Error deleting team:', error);
        const Zzerdal = new Zerdal();
        Zzerdal.show('Failed to delete team', 'error');
    }
}

async function editMember(UserID, TeamID) {
    const Zzerdal = new Zerdal();
    try {
        const memberResponse = await ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].GET_MEMBER_BY_TEAM_IDANDUSER_ID, {
            teamId: TeamID,
            userId: UserID
        });

        if (!memberResponse.success) {
            throw new Error('Failed to fetch member details');
        }

        const member = memberResponse.data;
        editingMemberId = UserID;
        modalTitle.textContent = 'Edit Team Member';
        submitBtn.textContent = 'Save Changes';

        // Fill form with member data
        document.getElementById('userId').value = member.UserID || '';
        document.getElementById('phoneNumber').value = member.PhoneNumber || '';
        document.getElementById('roleId').value = member.RoleID;
        modal.classList.add('active');
    } catch (error) {
        console.error('Error editing member:', error);
        Zzerdal.show('Failed to load member details: ' + error.message, 'error');
    }
    closeAllDropdowns();
}

async function deleteMember(UserID, TeamID) {
    const Zzerdal = new Zerdal();
    try {
        await ApiUtil.handleApiCallModal(ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].REMOVE_MEMBER, {
            teamId: TeamID, userId: UserID
        }), Zzerdal, "Member has been removed from the team.");

        // Refresh the team members list
        const team = teams.find(t => t.TeamID.toString() === TeamID);
        if (team) {
            await renderTeamMembers(team);
        }
    } catch (error) {
        console.error('Error deleting member:', error);
        Zzerdal.show('Failed to remove member: ' + error.message, 'error');
    }
    closeAllDropdowns();
}

function closeModal() {
    modal.classList.remove('active');
    editingMemberId = null;
}

function getInitials(name) {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase();
}

// Close modal when clicking outside
modal.addEventListener('click', (e) => {
    if (e.target === modal) {
        closeModal();
    }
});

createTeamBtn.addEventListener('click', () => {
    createTeamModal.classList.add('active');
});

createTeamForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const Zzerdal = new Zerdal();

    try {
        // Get user ID
        const userResponse = await ApiUtil.callApi(Entity.USER, Actions[Entity.USER].GET_USER_ID);
        if (!userResponse.success) {
            throw new Error('Failed to fetch user ID');
        }

        // Get form data and add owner ID
        const form = document.getElementById('createTeamForm');
        const formData = new FormData(form);
        formData.append('OwnerID', userResponse.data);
        const jsonData = Object.fromEntries(formData.entries());

        // Create team with proper error handling
        await ApiUtil.handleApiCallModal(ApiUtil.callApi(Entity.TEAM, Actions[Entity.TEAM].CREATE_TEAM, jsonData), Zzerdal, "New team has been created.");

        // Refresh teams list and close modal
        await initializeTeams();
        closeCreateTeamModal();
        createTeamForm.reset();
    } catch (error) {
        console.error('Error creating team:', error);
        Zzerdal.show('Failed to create team: ' + error.message, 'error');
    }
});

function closeCreateTeamModal() {
    createTeamModal.classList.remove('active');
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.actions-cell')) {
        closeAllDropdowns();
    }
    if (e.target === modal) {
        closeModal();
    }
    if (e.target === createTeamModal) {
        closeCreateTeamModal();
    }
});

function toggleDropdown(event) {
    event.stopPropagation();
    const actionsCell = event.target.closest('.actions-cell');
    const dropdown = actionsCell.querySelector('.dropdown-menu');

    closeAllDropdowns();

    // Toggle the clicked dropdown
    dropdown.classList.toggle('active');
}

function closeAllDropdowns() {
    document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
        dropdown.classList.remove('active');
    });
}

// Initialize with first team's members
initializeTeams();

function goToHome() {
    window.location.href = '../index.html';  // This will redirect to the root URL
}