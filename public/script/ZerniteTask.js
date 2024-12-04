async function checkLoginStatus() { // boolean
    const response = await ApiUtil.callApi(Entity.USER, Actions[Entity.USER].IS_LOGIN);
    return response.success ? response.data : false;
}
async function getUserInfo() {
    const response = await ApiUtil.callApi(Entity.USER, Actions[Entity.USER].GET_USER_INFO);
    return response.success ? response.data : null;
}
async function redirectLogin(path) {
    const islogin = await checkLoginStatus();
    if (!islogin) {
        window.location.href = path;
    }
}