const chatBox = document.getElementById('chat-box');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');
const imageInput = document.getElementById('imageInput');
const selectedImageName = document.getElementById('selectedImageName');
let chatEnabled = true;
let closedNoticeShown = false;
const blockedLocalMessages = [];

function getCurrentTimeLabel() {
    const now = new Date();
    return now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
}

function appendBlockedLocalMessage(text) {
    blockedLocalMessages.push({
        message: text,
        time: getCurrentTimeLabel(),
        user: 'Tú',
    });
}

function renderBlockedLocalMessages() {
    blockedLocalMessages.forEach(msg => {
        const div = document.createElement('div');
        div.className = 'message blocked';

        const textHtml = msg.message ? `<p>${msg.message}</p>` : '';
        div.innerHTML = `
            <div class="meta">${msg.user} - ${msg.time}</div>
            ${textHtml}
            <p class="blocked-note">Mensaje no enviado por spam</p>
        `;

        chatBox.appendChild(div);
    });
}

function setChatEnabled(enabled) {
    chatEnabled = enabled;
    if (!chatForm) return;

    const controls = chatForm.querySelectorAll('input, button');
    controls.forEach(control => {
        control.disabled = !enabled;
    });
}

function showClosedNotice(state) {
    if (closedNoticeShown || !chatBox) return;
    const div = document.createElement('div');
    div.className = 'message system';
    div.innerHTML = `<p>El chat fue cerrado por el profesor (estado: ${state}).</p>`;
    chatBox.appendChild(div);
    closedNoticeShown = true;
}

// Mostrar el nombre del archivo seleccionado (si existe)
if (imageInput && selectedImageName) {
    imageInput.addEventListener('change', () => {
        const selectedFile = imageInput.files && imageInput.files[0];
        selectedImageName.textContent = selectedFile
            ? selectedFile.name
            : 'Ninguna imagen seleccionada';
    });
}

// CARGAR MENSAJES (Polling)
async function loadMessages() {
    try {
        // Alumno llama sin parámetros -> el PHP asume su propia sesión
        const response = await fetch('../api/leer.php'); 
        const messages = await response.json();
        const chatState = response.headers.get('X-Chat-State');

        if (chatState && chatState !== 'abierto') {
            setChatEnabled(false);
        } else {
            setChatEnabled(true);
            closedNoticeShown = false;
        }

        chatBox.innerHTML = ''; // Limpiamos para redibujar (simple version)

        messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = `message ${msg.is_me ? 'mine' : 'other'}`;
            div.innerHTML = `
                <div class="meta">${msg.user} - ${msg.time}</div>
                ${msg.message}
            `;

            // Si hay una imagen, la mostramos debajo del mensaje
            const textHtml = msg.message ? `<p>${msg.message}</p>` : '';
            const imageHtml = msg.image_url
                ? `<img src="${msg.image_url}" alt="Imagen enviada" class="chat-image">`
                : '';

            div.innerHTML = `
                <div class="meta">${msg.user} - ${msg.time}</div>
                ${textHtml}
                ${imageHtml}
            `;

            chatBox.appendChild(div);
        });

        renderBlockedLocalMessages();

        if (chatState && chatState !== 'abierto') {
            showClosedNotice(chatState);
        }

        
        // Auto scroll abajo (opcional, pero recomendado)
        // chatBox.scrollTop = chatBox.scrollHeight;

    } catch (error) {
        console.error("Error cargando chat:", error);
    }
}

// ENVIAR MENSAJE
chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!chatEnabled) return;

    const text = messageInput.value.trim();
    const selectedFile = imageInput && imageInput.files ? imageInput.files[0] : null;

    // Validación básica: no enviar si no hay texto ni imagen
    if (!text && !selectedFile) return;

    // Preparamos el FormData para enviar el mensaje y la imagen (si existe)
    const formData = new FormData();
    formData.append('message', text);


    // Si hay una imagen seleccionada, la agregamos al FormData
    if (selectedFile) {
        formData.append('image', selectedFile);
    }

    // Enviamos el mensaje al backend
    const response = await fetch('../api/enviar.php', {
        method: 'POST',
        body: formData
    });

    // El backend devuelve un JSON con { success: true/false, message: "..." }
    const result = await response.json();
    if (!result.success) {
        if (typeof result.message === 'string' && result.message.toLowerCase().includes('spam')) {
            appendBlockedLocalMessage(text);
            messageInput.value = '';
            if (imageInput) {
                imageInput.value = '';
            }
            if (selectedImageName) {
                selectedImageName.textContent = 'Ninguna imagen seleccionada';
            }
            loadMessages();
            return;
        }

        console.error('Error enviando mensaje:', result.message);
        return;
    }

    // Limpiamos el input de texto y la imagen seleccionada
    messageInput.value = '';
    if (imageInput) {
        imageInput.value = '';
    }
    if (selectedImageName) {
        selectedImageName.textContent = 'Ninguna imagen seleccionada';
    }

    loadMessages(); // Refrescar al momento
});

// Auto-refresco cada 2 segundos
setInterval(loadMessages, 2000);
loadMessages();
