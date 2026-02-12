<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Chat Docker</title>
    <!--style>
        #chat-box { width: 400px; height: 300px; border: 1px solid #ccc; overflow-y: scroll; padding: 10px; background: #f9f9f9; margin-bottom: 10px; }
        .msg { background: #e1ffc7; padding: 8px; margin: 5px 0; border-radius: 5px; font-family: sans-serif; }
        .spam { background: #ffcccc; }
    </style-->
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        h2 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 300;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        #chat-box {
            width: 100%;
            max-width: 500px;
            height: 400px;
            background: white;
            border-radius: 20px;
            padding: 20px;
            overflow-y: auto;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .msg {
            background: #e1ffc7;
            padding: 12px 16px;
            border-radius: 18px 18px 18px 4px;
            margin: 0;
            max-width: 75%;
            word-wrap: break-word;
            align-self: flex-start;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            position: relative;
            animation: fadeIn 0.3s ease;
        }

        .msg::before {
            content: '';
            position: absolute;
            left: -8px;
            bottom: 0;
            width: 0;
            height: 0;
            border: 8px solid transparent;
            border-right-color: #e1ffc7;
            border-left: 0;
            border-bottom: 0;
        }

        .spam {
            background: #ffcccc;
            border-radius: 18px 18px 18px 4px;
            align-self: flex-start;
            color: #5a1a1a;
            border: 1px solid #ffaaaa;
        }

        .spam::before {
            border-right-color: #ffcccc;
        }

        /* Simulando mensajes del usuario (alineados a la derecha) */
        .msg:nth-child(odd):not(.spam) {
            background: #007aff;
            color: white;
            border-radius: 18px 18px 4px 18px;
            align-self: flex-end;
        }

        .msg:nth-child(odd):not(.spam)::before {
            left: auto;
            right: -8px;
            border-left-color: #007aff;
            border-right: 0;
            border-bottom: 0;
        }

        #chat-form {
            display: flex;
            gap: 10px;
            width: 100%;
            max-width: 500px;
        }

        #mensaje {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        #mensaje:focus {
            outline: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            transform: translateY(-2px);
        }

        button[type="submit"] {
            background: #007aff;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 0 30px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,122,255,0.3);
        }

        button[type="submit"]:hover {
            background: #0056cc;
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(0,122,255,0.4);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        /* Scrollbar personalizada */
        #chat-box::-webkit-scrollbar {
            width: 8px;
        }

        #chat-box::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #chat-box::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        #chat-box::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Animación para nuevos mensajes */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Indicador de mensajes nuevos */
        .new-msg-indicator {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin: 10px 0;
            font-style: italic;
        }
    </style>
</head>
<body>

    <h2>Chat Personal</h2>
    <div id="chat-box">
        <?php
        require_once 'db.php';
        $stmt = $pdo->query("SELECT contenido, tipo_filtro FROM mensajes WHERE esta_borrado = 0");
        while ($row = $stmt->fetch()) {
            $clase = ($row['tipo_filtro'] == 'SPAM') ? 'msg spam' : 'msg';
            echo "<div class='$clase'>" . htmlspecialchars($row['contenido']) . "</div>";
        }
        ?>
    </div>

    <form id="chat-form">
        <input type="text" id="mensaje" placeholder="Escribe algo..." required style="width: 300px;">
        <button type="submit">Enviar</button>
    </form>

    <script>
        const form = document.getElementById('chat-form');
        const chatBox = document.getElementById('chat-box');

        form.onsubmit = async (e) => {
            e.preventDefault();
            const input = document.getElementById('mensaje');
            const texto = input.value;

            // Enviar datos a PHP mediante Fetch
            const response = await fetch('guardar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ contenido: texto })
            });

            const result = await response.json();

            if (result.status === 'success') {
                // Agregar el mensaje visualmente con la clase correcta
                const div = document.createElement('div');
                div.className = result.tipo === 'SPAM' ? 'msg spam' : 'msg';
                div.textContent = texto;
                chatBox.appendChild(div);
                chatBox.scrollTop = chatBox.scrollHeight;
                input.value = '';
            }
        };
    </script>
</body>
</html>