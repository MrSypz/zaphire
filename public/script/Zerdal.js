class Zerdal {
    constructor() {
        this.createAlert();
    }

    createAlert() {
        this.alertBox = document.createElement("div");
        this.alertBox.id = "zerdal-alert";
        this.alertBox.className = "zerdal-alert";
        this.alertBox.style.display = "none";

        this.alertBox.innerHTML = `
                <div class="alert-content">
                    <p id="alert-message"></p>
                    <button class="agree-btn" id="alert-ok-button">OK</button>
                </div>
            `;

        document.body.appendChild(this.alertBox);

        this.alertMessage = this.alertBox.querySelector("#alert-message");
        this.alertOkButton = this.alertBox.querySelector("#alert-ok-button");

        this.alertOkButton.addEventListener("click", () => {
            this.hide();
        });
    }

    show(message, type = "info") {
        this.alertMessage.textContent = message;

        const colors = {
            success: "green",
            error: "red",
            info: "blue",
        };
        this.alertMessage.style.color = colors[type] || colors.info;

        this.alertBox.style.display = "flex";
    }

    hide() {
        this.alertBox.style.display = "none";
    }
}

class AnimatedModal {
    constructor(options = {}) {
        this.options = {
            title: options.title || 'Notification',
            message: options.message || '',
            buttonText: options.buttonText || 'OK',
            autoClose: options.autoClose || false,
            autoCloseTime: options.autoCloseTime || 3000,
            onClose: options.onClose || null,
            type: options.type || 'info'
        };

        this.createModal();
        this.setupEventListeners();
    }

    createModal() {
        this.modal = document.createElement('div');
        this.modal.className = 'animated-modal';
        this.modal.innerHTML = `
            <div class="modal-content">
                <h2>${this.options.title}</h2>
                <p>${this.options.message}</p>
                <button class="modal-button">${this.options.buttonText}</button>
            </div>
        `;
        document.body.appendChild(this.modal);

        this.closeButton = this.modal.querySelector('.modal-button');
    }

    setupEventListeners() {
        this.closeButton.addEventListener('click', () => this.close());
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });
    }

    show() {
        this.modal.style.display = 'flex';
        setTimeout(() => {
            this.modal.classList.add('show');
            this.modal.querySelector('.modal-content').classList.add('show');
        }, 10);

        if (this.options.autoClose) {
            setTimeout(() => this.close(), this.options.autoCloseTime);
        }
    }

    close() {
        this.modal.classList.remove('show');
        this.modal.querySelector('.modal-content').classList.remove('show');
        setTimeout(() => {
            this.modal.style.display = 'none';
            if (typeof this.options.onClose === 'function') {
                this.options.onClose();
            }
        }, 300);
    }

    updateContent(title, message) {
        this.modal.querySelector('h2').textContent = title;
        this.modal.querySelector('p').textContent = message;
    }
}