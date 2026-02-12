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