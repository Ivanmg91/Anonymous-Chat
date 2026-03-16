const profesoresList = document.getElementById('profesoresList');
const alumnosList = document.getElementById('alumnosList');
const createForm = document.getElementById('adminCreateForm');
const adminMessage = document.getElementById('adminMessage');

const newUserInput = document.getElementById('newUser');
const newPasswordInput = document.getElementById('newPassword');
const newRoleSelect = document.getElementById('newRole');

const refreshProfesoresBtn = document.getElementById('refreshProfesores');
const refreshAlumnosBtn = document.getElementById('refreshAlumnos');
const refreshChatsBtn = document.getElementById('refreshChats');
const chatRoomSelect = document.getElementById('chatRoomSelect');
const chatStateSelect = document.getElementById('chatStateSelect');
const applyChatStateBtn = document.getElementById('applyChatState');
const adminChatMeta = document.getElementById('adminChatMeta');
const adminChatMessages = document.getElementById('adminChatMessages');

let chatsCache = [];
let statesCache = [];

async function apiRequest(method, payload) {
    const res = await fetch('../api/admin_users.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: payload ? JSON.stringify(payload) : undefined
    });

    const data = await res.json();
    return { ok: res.ok, data };
}

async function chatApiRequest(method, payload) {
    const config = {
        method,
        headers: { 'Content-Type': 'application/json' },
    };

    if (payload && method !== 'GET') {
        config.body = JSON.stringify(payload);
    }

    const endpoint = method === 'GET' && payload && payload.chat_room
        ? `../api/admin_chats.php?chat_room=${encodeURIComponent(payload.chat_room)}`
        : '../api/admin_chats.php';

    const res = await fetch(endpoint, config);
    const data = await res.json();
    return { ok: res.ok, data };
}

function showMessage(text, isError = false) {
    adminMessage.textContent = text;
    adminMessage.className = isError ? 'admin-message error' : 'admin-message success';
}

function renderUserList(container, users, roleLabel) {
    container.innerHTML = '';

    if (!users || users.length === 0) {
        const empty = document.createElement('li');
        empty.className = 'admin-empty';
        empty.textContent = `No hay ${roleLabel}s`;
        container.appendChild(empty);
        return;
    }

    users.forEach((user) => {
        const li = document.createElement('li');
        li.className = 'admin-user-item';

        const userInfo = document.createElement('div');
        userInfo.className = 'admin-user-info';
        userInfo.textContent = `${user.user} (#${user.id})`;

        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-small admin-delete-btn';
        deleteBtn.textContent = 'Borrar';
        deleteBtn.addEventListener('click', () => deleteUser(user.id, user.user));

        li.appendChild(userInfo);
        li.appendChild(deleteBtn);
        container.appendChild(li);
    });
}

function renderChatOptions(chats) {
    if (!chatRoomSelect) return;

    chatRoomSelect.innerHTML = '';

    if (!chats || chats.length === 0) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No hay chats disponibles';
        chatRoomSelect.appendChild(option);
        return;
    }

    chats.forEach((chat) => {
        const option = document.createElement('option');
        option.value = chat.room_key;
        const teachers = chat.teacher_names || 'sin profesor';
        option.textContent = `${chat.student_name} | ${teachers} | ${chat.estado}`;
        chatRoomSelect.appendChild(option);
    });
}

function renderStateOptions(states) {
    if (!chatStateSelect) return;

    chatStateSelect.innerHTML = '';
    states.forEach((state) => {
        const option = document.createElement('option');
        option.value = state;
        option.textContent = state;
        chatStateSelect.appendChild(option);
    });
}

function renderChatMeta(chat) {
    if (!adminChatMeta) return;

    if (!chat) {
        adminChatMeta.textContent = 'Selecciona un chat para ver su historial.';
        return;
    }

    const teachers = chat.teacher_names || 'sin profesor';
    const totalMessages = Number(chat.message_count || 0);
    adminChatMeta.textContent = `Alumno: ${chat.student_name} | Profesores: ${teachers} | Mensajes: ${totalMessages} | Estado actual: ${chat.estado}`;
}

function renderChatMessages(messages) {
    if (!adminChatMessages) return;

    adminChatMessages.innerHTML = '';

    if (!messages || messages.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'admin-empty';
        empty.textContent = 'Este chat no tiene mensajes todavía.';
        adminChatMessages.appendChild(empty);
        return;
    }

    messages.forEach((msg) => {
        const item = document.createElement('div');
        item.className = `admin-chat-message ${msg.role || ''}`;

        const textHtml = msg.message ? `<p>${msg.message}</p>` : '';
        const imageHtml = msg.image_url
            ? `<img src="${msg.image_url}" alt="Imagen enviada" class="chat-image">`
            : '';

        item.innerHTML = `
            <div class="meta">${msg.user} (${msg.role}) - ${msg.time}</div>
            ${textHtml}
            ${imageHtml}
        `;

        adminChatMessages.appendChild(item);
    });

    adminChatMessages.scrollTop = adminChatMessages.scrollHeight;
}

function getSelectedChat() {
    if (!chatRoomSelect) return null;
    const key = chatRoomSelect.value;
    return chatsCache.find((chat) => chat.room_key === key) || null;
}

async function loadChatMessages(chatRoomKey) {
    if (!chatRoomKey) {
        renderChatMessages([]);
        return;
    }

    const { ok, data } = await chatApiRequest('GET', { chat_room: chatRoomKey });

    if (!ok || !data.success) {
        showMessage(data.message || 'No se pudieron cargar los mensajes del chat', true);
        return;
    }

    renderChatMessages(data.messages);
}

async function loadChats() {
    if (!chatRoomSelect || !chatStateSelect) return;

    const currentRoom = chatRoomSelect.value;
    const { ok, data } = await chatApiRequest('GET');

    if (!ok || !data.success) {
        showMessage(data.message || 'No se pudieron cargar los chats', true);
        return;
    }

    chatsCache = Array.isArray(data.chats) ? data.chats : [];
    statesCache = Array.isArray(data.states) ? data.states : [];

    renderChatOptions(chatsCache);
    renderStateOptions(statesCache);

    if (chatsCache.length === 0) {
        renderChatMeta(null);
        renderChatMessages([]);
        return;
    }

    const roomExists = chatsCache.some((chat) => chat.room_key === currentRoom);
    chatRoomSelect.value = roomExists ? currentRoom : chatsCache[0].room_key;

    const selectedChat = getSelectedChat();
    renderChatMeta(selectedChat);
    if (selectedChat && statesCache.includes(selectedChat.estado)) {
        chatStateSelect.value = selectedChat.estado;
    }

    await loadChatMessages(chatRoomSelect.value);
}

async function applyChatState() {
    const chatRoom = chatRoomSelect ? chatRoomSelect.value : '';
    const state = chatStateSelect ? chatStateSelect.value : '';

    if (!chatRoom || !state) {
        showMessage('Selecciona chat y estado', true);
        return;
    }

    const { ok, data } = await chatApiRequest('PATCH', { chat_room: chatRoom, state });

    if (!ok || !data.success) {
        showMessage(data.message || 'No se pudo actualizar el estado del chat', true);
        return;
    }

    showMessage(data.message || 'Estado actualizado');
    await loadChats();
}

async function loadUsers() {
    const { ok, data } = await apiRequest('GET');

    if (!ok || !data.success) {
        const msg = data.message || 'No se pudieron cargar usuarios';
        showMessage(msg, true);
        return;
    }

    renderUserList(profesoresList, data.users.profesor, 'profesor');
    renderUserList(alumnosList, data.users.alumno, 'alumno');
}

async function deleteUser(id, username) {
    const confirmed = window.confirm(`Vas a borrar a ${username}. Esta accion no se puede deshacer.`);
    if (!confirmed) {
        return;
    }

    const { ok, data } = await apiRequest('DELETE', { id });
    if (!ok || !data.success) {
        showMessage(data.message || 'No se pudo borrar el usuario', true);
        return;
    }

    showMessage(data.message || 'Usuario eliminado');
    await loadUsers();
}

createForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const user = newUserInput.value.trim();
    const password = newPasswordInput.value;
    const role = newRoleSelect.value;

    if (!user || !password || !role) {
        showMessage('Completa todos los campos', true);
        return;
    }

    const { ok, data } = await apiRequest('POST', { user, password, role });

    if (!ok || !data.success) {
        showMessage(data.message || 'No se pudo crear el usuario', true);
        return;
    }

    showMessage(data.message || 'Usuario creado correctamente');
    createForm.reset();
    await loadUsers();
});

refreshProfesoresBtn.addEventListener('click', loadUsers);
refreshAlumnosBtn.addEventListener('click', loadUsers);

if (refreshChatsBtn) {
    refreshChatsBtn.addEventListener('click', () => {
        loadChats().catch(() => showMessage('Error al recargar chats', true));
    });
}

if (chatRoomSelect) {
    chatRoomSelect.addEventListener('change', () => {
        const selectedChat = getSelectedChat();
        renderChatMeta(selectedChat);
        if (selectedChat && chatStateSelect && statesCache.includes(selectedChat.estado)) {
            chatStateSelect.value = selectedChat.estado;
        }
        loadChatMessages(chatRoomSelect.value).catch(() => showMessage('Error al cargar historial del chat', true));
    });
}

if (applyChatStateBtn) {
    applyChatStateBtn.addEventListener('click', () => {
        applyChatState().catch(() => showMessage('Error al cambiar el estado', true));
    });
}

Promise.all([
    loadUsers(),
    loadChats(),
]).catch(() => showMessage('Error al cargar datos iniciales', true));
