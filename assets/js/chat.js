const chatBox = document.getElementById('chat-box');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');

// 1. CARGAR MENSAJES (Polling)
async function loadMessages() {
    try {
        // Alumno llama sin parámetros -> el PHP asume su propia sesión
        const response = await fetch('../api/leer.php'); 
        const messages = await response.json();

        chatBox.innerHTML = ''; // Limpiamos para redibujar (simple version)

        messages.forEach(msg => {
            const div = document.createElement('div');
            // msg.is_me viene del PHP (true si fui yo, false si fue el profe)
            div.className = `message ${msg.is_me ? 'mine' : 'other'}`;
            div.innerHTML = `
                <div class="meta">${msg.user} - ${msg.time}</div>
                ${msg.message}
            `;
            chatBox.appendChild(div);
        });
        
        // Auto scroll abajo (opcional, pero recomendado)
        // chatBox.scrollTop = chatBox.scrollHeight;

    } catch (error) {
        console.error("Error cargando chat:", error);
    }
}

// 2. ENVIAR MENSAJE
chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = messageInput.value.trim();
    if (!text) return;

    await fetch('../api/enviar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text }) // Alumno no necesita especificar sala
    });

    messageInput.value = '';
    loadMessages(); // Refrescar al momento
});

// Auto-refresco cada 2 segundos
setInterval(loadMessages, 2000);
loadMessages();