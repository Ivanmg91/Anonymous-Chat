document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault(); // Evita que la página se recargue
    
    const user = document.getElementById('user').value;
    const password = document.getElementById('password').value;
    const messageDiv = document.getElementById('message');
    
    messageDiv.textContent = 'Verificando...';
    
    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user, password })
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageDiv.textContent = 'Login correcto! Redirigiendo...';
            setTimeout(() => {
                window.location.href = 'views/welcome.php';
            }, 1000);
        } else {
            messageDiv.textContent = '❌ ' + result.message;
        }
        
    } catch (error) {
        messageDiv.textContent = '❌ Error de conexión';
        console.error('Error:', error);
    }
});

document.getElementById('btnAnon').addEventListener('click', async function() {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = 'Creando identidad anónima...';

    try {
        // Lamadda POST simple sin body
        const response = await fetch('api/anonymous_login.php', {
            method: 'POST'
        });

        const result = await response.json();

        if (result.success) {
            // MOstrar usuario generado
            messageDiv.innerHTML = 'Identidad creada: <b>' + result.user + '</b>. Entrando...';

            // Redirigir al chat
            setTimeout(() => {
                window.location.href = 'views/welcome.php';
            }, 1500);
        } else {
            messageDiv.textContent = 'Error: ' + result.message;
        }
    } catch (error) {
        messageDiv.textContent = 'Error de conexion';
        console.log(error);
    }
});