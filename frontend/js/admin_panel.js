const profesoresList = document.getElementById('profesoresList');
const alumnosList = document.getElementById('alumnosList');
const createForm = document.getElementById('adminCreateForm');
const adminMessage = document.getElementById('adminMessage');

const newUserInput = document.getElementById('newUser');
const newPasswordInput = document.getElementById('newPassword');
const newRoleSelect = document.getElementById('newRole');

const refreshProfesoresBtn = document.getElementById('refreshProfesores');
const refreshAlumnosBtn = document.getElementById('refreshAlumnos');

async function apiRequest(method, payload) {
    const res = await fetch('../api/admin_users.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: payload ? JSON.stringify(payload) : undefined
    });

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

loadUsers().catch(() => showMessage('Error al cargar datos iniciales', true));
