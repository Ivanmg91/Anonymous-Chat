let currentChatRoom = null;
let currentStudentName = null;

const usersList = document.getElementById('usersList');
const chatBox = document.getElementById('chat-box');
const chatTitle = document.getElementById('chatTitle');
const inputArea = document.getElementById('inputArea');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');

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
            li.textContent = room.student_name;
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
    inputArea.style.display = 'flex'; // Mostrar la caja de escribir
    loadMessages(); // Cargar mensajes inmediatamente
    loadUsers(); // Para actualizar el estilo visual 'active'
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
            chatBox.appendChild(div);
        });
    } catch (e) { console.error(e); }
}

// ENVIAR MENSAJE (COMO PROFESOR)
chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!currentChatRoom) return;

    const text = messageInput.value.trim();
    if (!text) return;

    await fetch('../api/enviar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            message: text,
            chat_room: currentChatRoom // ¡IMPORTANTE! Decimos a qué sala va la respuesta
        })
    });

    messageInput.value = '';
    loadMessages();
});

// Bucles de refresco
setInterval(loadUsers, 5000); // Actualizar lista de usuarios cada 5s
setInterval(loadMessages, 2000); // Actualizar mensajes cada 2s
loadUsers(); // Carga inicial
