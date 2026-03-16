let currentChatRoom = null;
let currentStudentName = null;
const blockedLocalMessagesByRoom = {};

const usersList = document.getElementById('usersList');
const chatBox = document.getElementById('chat-box');
const chatTitle = document.getElementById('chatTitle');
const inputArea = document.getElementById('inputArea');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');
const imageInput = document.getElementById('imageInput');
const selectedImageName = document.getElementById('selectedImageName');
const finalizeChatBtn = document.getElementById('finalizeChatBtn');
const reviewChatBtn = document.getElementById('reviewChatBtn');

function getCurrentTimeLabel() {
    const now = new Date();
    return now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
}

function appendBlockedLocalMessage(roomKey, text) {
    if (!blockedLocalMessagesByRoom[roomKey]) {
        blockedLocalMessagesByRoom[roomKey] = [];
    }

    blockedLocalMessagesByRoom[roomKey].push({
        message: text,
        time: getCurrentTimeLabel(),
        user: 'Tú',
    });
}

function renderBlockedLocalMessages(roomKey) {
    const roomMessages = blockedLocalMessagesByRoom[roomKey] || [];

    roomMessages.forEach(msg => {
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

function updateChatActionLinks() {
    const encodedRoom = encodeURIComponent(currentChatRoom);
    if (finalizeChatBtn) {
        finalizeChatBtn.href = `../api/change_chat_state.php?state=finalizado&chat_room=${encodedRoom}`;
    }
    if (reviewChatBtn) {
        reviewChatBtn.href = `../api/change_chat_state.php?state=revision&chat_room=${encodedRoom}`;
    }
}

// CARGAR LISTA DE ALUMNOS (API Nueva)
async function loadUsers() {
    try {
        const res = await fetch('../api/get_users.php');
        const users = await res.json();

        usersList.innerHTML = ''; // Limpiamos la lista
        
        if(users.error) {
            usersList.innerHTML = '<li style="padding:10px; color:red">Error acceso</li>';
            return;
        }

        users.forEach(room => {
            const li = document.createElement('li');
            li.className = `user-item ${room.room_key === currentChatRoom ? 'active' : ''}`;
            li.textContent = `${room.student_name} - ${room.estado}`;
            li.onclick = () => selectUser(room);
            usersList.appendChild(li);
        });
    } catch (e) { console.error(e); }
}

// SELECCIONAR UN ALUMNO
function selectUser(room) {
    currentChatRoom = room.room_key;
    currentStudentName = room.student_name;
    chatTitle.textContent = "Chat con: " + currentStudentName;
    updateChatActionLinks();
    inputArea.style.display = 'flex'; // Mostrar la caja de escribir
    loadMessages(); // Cargar mensajes inmediatamente
    loadUsers(); // Para actualizar el estilo visual 'active'

    finalizeChatBtn.style.visibility = 'visible';
    reviewChatBtn.style.visibility = 'visible';
}

// CARGAR MENSAJES DEL ALUMNO SELECCIONADO
async function loadMessages() {
    if (!currentChatRoom) return; // Si no he elegido a nadie, no hago nada

    try {
        // Aquí SÍ enviamos parámetro GET para leer la sala específica
        const res = await fetch(`../api/leer.php?chat_room=${currentChatRoom}`);
        const messages = await res.json();

        chatBox.innerHTML = '';
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

        renderBlockedLocalMessages(currentChatRoom);
    } catch (e) { console.error(e); }
}

// ENVIAR MENSAJE (COMO PROFESOR)
chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = messageInput.value.trim();
    const selectedFile = imageInput && imageInput.files ? imageInput.files[0] : null;

    // Validación básica: no enviar si no hay texto ni imagen
    if (!text && !selectedFile) return;

    // Preparamos el FormData para enviar el mensaje y la imagen (si existe)
    const formData = new FormData();
    formData.append('message', text);
    formData.append('chat_room', currentChatRoom);


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
            appendBlockedLocalMessage(currentChatRoom, text);
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

// Bucles de refresco
setInterval(loadUsers, 5000); // Actualizar lista de usuarios cada 5s
setInterval(loadMessages, 2000); // Actualizar mensajes cada 2s
updateChatActionLinks();
loadUsers(); // Carga inicial
