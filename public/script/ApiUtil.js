/**
 * <h1>Zernite Api Version 1.0</h1>
 * @author Thanawit Pidduakeaw
 * <br>
 * <p>API ช่วยในการ call เรียกข้อมูลจาก backend</p>
 */
let APIurl = "https://tyranus.online/backend/src/api/api.php"

class ApiUtil {
    static async sendRequest(url, method, data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('Request failed:', error);
            throw error;
        }
    }

    /**
     * Method เรียกหลักในการ callApi
     * @param {string} entity - ชื่อ class ที่ต้องการเรียก method ดูได้ที Entity
     * @param {string} action - ชื่อ method ที่ต้องการทำ ดูได้ที่ UserAction
     * @param {object|null} data - ข้อมูลที่ต้องการส่งไปยัง API (มีหรือไม่มีก็ได้)
     * @returns {Promise<any|undefined>}
     */
    static async callApi(entity, action, data = null) {
        if (!Actions[entity]) {
            throw new Error(`Invalid entity: ${entity}.`);
        }
        if (!Object.values(Actions[entity]).includes(action)) {
            throw new Error(`Invalid action: ${action} for entity: ${entity}.`);
        }

        const url = new URL(APIurl + `?entity=${entity}&action=${action}`);
        return this.sendRequest(url, data ? 'POST' : 'GET', data);
    }

    /**
     * <b>ตัวช่วยในการจัดการ API<b><br>
     * ตัวช่วยเรียก method แบบขี้เกียจเขียน
     * @param {Promise} apiCall - ตัว method callapi.
     * @param {string} resultElementId - ID ของ element นั้นๆ ที่ต้องการให้แสดงผล.
     */
    static async handleApiCall(apiCall, resultElementId) {
        const resultDiv = document.getElementById(resultElementId);

        try {
            const response = await apiCall;
            if (response.success) {
                resultDiv.innerHTML = `<p style="color: green;"><strong>Success:</strong> ${response.message}</p>`;
            } else {
                resultDiv.innerHTML = `<p style="color: red;"><strong>Error:</strong> ${response.error}</p>`;
            }
        } catch (error) {
            resultDiv.innerHTML = `<p style="color: red;"><strong>Error:</strong> ${error.message}</p>`;
        }
    }
    static async handleApiCallModal(apiCall, zerdal, extramessage = null) {
        try {
            const response = await apiCall;
            if (response.success) {
                const successMessage = extramessage ? `Success ${response.message} ${extramessage}` : `Success ${response.message}`;
                zerdal.show(successMessage, "success");
            } else {
                const errorMessage = extramessage ? `Error: ${response.error} ${extramessage}` : `Error: ${response.error}`;
                zerdal.show(errorMessage, "error");
            }
        } catch (error) {
            const errorMessage = extramessage ? `Error: ${error.message} ${extramessage}` : `Error: ${error.message}`;
            zerdal.show(errorMessage, "error");
        }
    }
}

/**
 * <b> Enum ช่วยเรียกต่างๆ </b>
 * @type {Readonly<{USER: string}>}
 */
const Entity = Object.freeze({
    USER: "User",
    TEAM: "Team"
});
/**
 * <b>Enum ที่ใช้เรียก ฟังชั่นจาก backend</b>
 */
const Actions = Object.freeze({
    [Entity.USER]: {
        LOGIN: "login",
        LOGOUT: "logout",
        CREATE_USER: "createUser",
        DELETE_USER: "deleteUser",
        UPDATE_USER: "updateUser",
        IS_LOGIN: "isLogin",
        GET_ROLE_ID: "getRoleID",
        GET_USER_ID: 'getUserID',
        GET_USER_NAME: 'getUserName',
        GET_USER_INFO: 'getUserInfo',
    },
    [Entity.TEAM]: {
        CREATE_TEAM: "createTeam",
        DELETE_TEAM: "deleteTeam",
        GET_OWNED_TEAMS: "getOwnedTeams",
        GET_OWNER_NAME: "getOwnerName",
        GET_MEMBERS_BY_TEAM_ID: "getMemberByTeamID",
        GET_MEMBER_BY_TEAM_IDANDUSER_ID :"getMemberByTeamAndUserID",
        ADD_MEMBER: "addMember",
        EDIT_MEMBER: "editMember",
        REMOVE_MEMBER: "removeMember"
    }
});

