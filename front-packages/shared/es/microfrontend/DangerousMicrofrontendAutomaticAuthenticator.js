var authentication_in_progress = false;
export var DangerousMicrofrontendAutomaticAuthenticator = {
    enable: function (username, password) {
        if (username === void 0) { username = 'admin'; }
        if (password === void 0) { password = 'admin'; }
        window.addEventListener('unhandledrejection', function (e) {
            var _a;
            if (((_a = e.reason) === null || _a === void 0 ? void 0 : _a.toString()) !== 'Error: You are not logged in the PIM') {
                return;
            }
            if (authentication_in_progress) {
                return;
            }
            authentication_in_progress = true;
            fetch('/user/login')
                .then(function (response) { return response.text(); })
                .then(function (html) {
                var parser = new DOMParser();
                var dom = parser.parseFromString(html, 'text/html');
                var input = dom.querySelector('input[name="_csrf_token"]');
                var csrf = input === null || input === void 0 ? void 0 : input.getAttribute('value');
                if (!csrf) {
                    console.error('Cannot find a CSRF token in the login page');
                    return;
                }
                var form = new FormData();
                form.append('_username', username);
                form.append('_password', password);
                form.append('_submit', '');
                form.append('_target_path', '');
                form.append('_csrf_token', csrf);
                fetch('/user/login-check', {
                    method: 'POST',
                    body: form,
                })
                    .finally(function () {
                    fetch('/rest/user/').then(function (response) {
                        if (response.ok) {
                            location.reload();
                        }
                        else {
                            console.error("Cannot login automatically with the credentials: ".concat(username, "/").concat(password));
                        }
                    });
                });
            });
        });
    },
};
//# sourceMappingURL=DangerousMicrofrontendAutomaticAuthenticator.js.map