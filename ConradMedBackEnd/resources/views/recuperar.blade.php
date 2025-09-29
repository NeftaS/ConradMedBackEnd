<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ConradMed · Recuperación de contraseña (Doctores)</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
  <div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold">Recuperación de contraseña · Doctores</h1>
        <p class="text-sm text-slate-600">Flujo: pedir código → validar → cambiar contraseña</p>
      </div>

      <!-- Config rápida -->
      <div class="mb-6 p-4 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <label class="block text-sm font-semibold mb-2">API Base</label>
        <input id="apiBase" class="w-full px-3 py-2 rounded-xl ring-1 ring-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none" value="http://localhost:8000" />
        <p class="mt-1 text-xs text-slate-500">Asegúrate que tu backend Laravel responde en esta URL.</p>
      </div>

      <!-- Paso 1: Solicitar código -->
      <div class="mb-4 p-4 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold mb-3">1) Solicitar código</h2>
        <div class="grid gap-3 sm:grid-cols-[1fr_auto] items-end">
          <div>
            <label class="block text-sm font-medium mb-1" for="telefono">Teléfono del doctor</label>
            <input id="telefono" type="text" placeholder="5551234567" class="w-full px-3 py-2 rounded-xl ring-1 ring-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none" />
          </div>
          <button id="btnRequest" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 disabled:opacity-50">Enviar código</button>
        </div>
        <p id="cooldown" class="mt-2 text-xs text-slate-500 hidden"></p>
      </div>

      <!-- Paso 2: Validar código (opcional) -->
      <div class="mb-4 p-4 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold mb-3">2) Validar código (opcional)</h2>
        <div class="grid gap-3 sm:grid-cols-[1fr_auto] items-end">
          <div>
            <label class="block text-sm font-medium mb-1" for="code">Código de 6 dígitos</label>
            <input id="code" type="text" placeholder="000000" class="w-full px-3 py-2 rounded-xl ring-1 ring-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none" />
          </div>
          <button id="btnVerify" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 disabled:opacity-50">Validar</button>
        </div>
        <p class="mt-2 text-xs text-slate-500">Este paso solo confirma que tu código está vigente; puedes saltarlo e ir directo al cambio.</p>
      </div>

      <!-- Paso 3: Cambiar contraseña -->
      <div class="mb-6 p-4 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold mb-3">3) Cambiar contraseña</h2>
        <div class="grid gap-3">
          <div class="grid sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium mb-1" for="password">Nueva contraseña</label>
              <input id="password" type="password" class="w-full px-3 py-2 rounded-xl ring-1 ring-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="password2">Confirmación</label>
              <input id="password2" type="password" class="w-full px-3 py-2 rounded-xl ring-1 ring-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none" />
            </div>
          </div>
          <button id="btnReset" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-medium hover:bg-slate-800 disabled:opacity-50 w-full sm:w-auto">Cambiar contraseña</button>
        </div>
      </div>

      <!-- Resultado / alertas -->
      <div id="alert" class="hidden p-4 rounded-2xl"></div>

      <footer class="mt-6 text-xs text-slate-500 text-center">ConradMed · Demo de recuperación</footer>
    </div>
  </div>

  <script>
    const api = () => document.getElementById('apiBase').value.replace(/\/$/, '');
    const telefonoEl = document.getElementById('telefono');
    const codeEl = document.getElementById('code');
    const passEl = document.getElementById('password');
    const pass2El = document.getElementById('password2');
    const alertBox = document.getElementById('alert');
    const cooldownEl = document.getElementById('cooldown');

    const showAlert = (msg, type = 'info') => {
      alertBox.className = 'p-4 rounded-2xl mt-2 ' + (type === 'error' ? 'bg-rose-50 text-rose-800 ring-1 ring-rose-200' : type === 'success' ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200' : 'bg-indigo-50 text-indigo-800 ring-1 ring-indigo-200');
      alertBox.textContent = msg;
      alertBox.classList.remove('hidden');
    };
    const hideAlert = () => alertBox.classList.add('hidden');

    // Cooldown para reenviar código
    let cooldown = 0, cooldownTimer = null;
    function startCooldown(seconds = 60) {
      cooldown = seconds;
      cooldownEl.classList.remove('hidden');
      const btn = document.getElementById('btnRequest');
      btn.disabled = true;
      cooldownEl.textContent = `Puedes volver a pedir código en ${cooldown}s`;
      clearInterval(cooldownTimer);
      cooldownTimer = setInterval(() => {
        cooldown--;
        cooldownEl.textContent = `Puedes volver a pedir código en ${cooldown}s`;
        if (cooldown <= 0) {
          clearInterval(cooldownTimer);
          cooldownEl.classList.add('hidden');
          btn.disabled = false;
        }
      }, 1000);
    }

    async function jsonFetch(url, body) {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
      });
      let data = {};
      try { data = await res.json(); } catch (_) {}
      return { ok: res.ok, status: res.status, data };
    }

    function extractError(data) {
      if (!data) return 'Error desconocido';
      if (typeof data === 'string') return data;
      if (data.error && typeof data.error === 'object') {
        const msgs = [];
        Object.values(data.error).forEach(arr => {
          if (Array.isArray(arr)) msgs.push(...arr);
          else if (typeof arr === 'string') msgs.push(arr);
        });
        if (msgs.length) return msgs.join('\n');
      }
      if (data.message) return data.message;
      return 'Solicitud rechazada';
    }

    // Paso 1: solicitar código
    document.getElementById('btnRequest').addEventListener('click', async () => {
      hideAlert();
      const telefono = telefonoEl.value.trim();
      if (!telefono) return showAlert('Ingresa el teléfono del doctor.', 'error');
      const { ok, data } = await jsonFetch(`${api()}/api/password/request-code`, { telefono });
      if (ok) {
        showAlert('Si el teléfono existe, se envió un código al correo registrado.', 'success');
        startCooldown(60);
      } else {
        showAlert(extractError(data), 'error');
      }
    });

    // Paso 2: validar código (opcional)
    document.getElementById('btnVerify').addEventListener('click', async () => {
      hideAlert();
      const telefono = telefonoEl.value.trim();
      const code = codeEl.value.trim();
      if (!telefono || !code) return showAlert('Escribe teléfono y código.', 'error');
      const { ok, data } = await jsonFetch(`${api()}/api/password/verify-code`, { telefono, code });
      if (ok) showAlert('Código válido ✅', 'success');
      else showAlert(extractError(data), 'error');
    });

    // Paso 3: reset
    document.getElementById('btnReset').addEventListener('click', async () => {
      hideAlert();
      const telefono = telefonoEl.value.trim();
      const code = codeEl.value.trim();
      const password = passEl.value;
      const password_confirmation = pass2El.value;
      if (!telefono || !code) return showAlert('Necesitas teléfono y código.', 'error');
      if (!password || password.length < 8) return showAlert('La contraseña debe tener al menos 8 caracteres.', 'error');
      if (password !== password_confirmation) return showAlert('La confirmación no coincide.', 'error');

      const btn = document.getElementById('btnReset');
      btn.disabled = true;
      const { ok, data } = await jsonFetch(`${api()}/api/password/reset`, { telefono, code, password, password_confirmation });
      btn.disabled = false;

      if (ok) {
        showAlert('Contraseña actualizada correctamente. Ya puedes iniciar sesión.', 'success');
        passEl.value = pass2El.value = '';
      } else {
        showAlert(extractError(data), 'error');
      }
    });
  </script>
</body>
</html>
