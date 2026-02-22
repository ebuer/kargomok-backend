<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Şifre Sıfırla - {{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f4f4f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 2rem; width: 100%; max-width: 400px; }
        h1 { font-size: 1.25rem; margin-bottom: 1.5rem; color: #18181b; }
        label { display: block; font-size: 0.875rem; font-weight: 500; color: #3f3f46; margin-bottom: 0.375rem; }
        input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d4d4d8; border-radius: 6px; font-size: 1rem; margin-bottom: 1rem; }
        input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.2); }
        button { width: 100%; padding: 0.625rem 1rem; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-size: 1rem; font-weight: 500; cursor: pointer; }
        button:hover { background: #2563eb; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        .message { padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
        .message.success { background: #dcfce7; color: #166534; }
        .message.error { background: #fee2e2; color: #991b1b; }
        .hint { font-size: 0.75rem; color: #71717a; margin-top: -0.5rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Yeni şifre belirleyin</h1>

        <div id="message" class="message" style="display: none;"></div>

        <form id="form">
            @csrf
            <label for="email">E-posta</label>
            <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">

            <label for="token">Sıfırlama kodu (e-postanızdaki linkten gelir)</label>
            <input type="text" id="token" name="token" value="{{ old('token', $token) }}" required placeholder="Linke tıkladıysanız otomatik dolu olur">

            <label for="password">Yeni şifre</label>
            <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
            <p class="hint">En az 8 karakter</p>

            <label for="password_confirmation">Yeni şifre (tekrar)</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">

            <button type="submit" id="btn">Şifreyi güncelle</button>
        </form>
    </div>

    <script>
        const form = document.getElementById('form');
        const messageEl = document.getElementById('message');
        const btn = document.getElementById('btn');

        function showMessage(text, type) {
            messageEl.textContent = text;
            messageEl.className = 'message ' + type;
            messageEl.style.display = 'block';
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            btn.disabled = true;
            messageEl.style.display = 'none';

            const payload = {
                email: document.getElementById('email').value,
                token: document.getElementById('token').value,
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value
            };

            try {
                const res = await fetch('/api/auth/reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json().catch(() => ({}));

                if (res.ok) {
                    showMessage(data.message || 'Şifreniz güncellendi.', 'success');
                    form.reset();
                    document.getElementById('email').value = payload.email;
                    document.getElementById('token').value = payload.token;
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Bir hata oluştu.');
                    showMessage(msg, 'error');
                }
            } catch (err) {
                showMessage('Bağlantı hatası. Lütfen tekrar deneyin.', 'error');
            }
            btn.disabled = false;
        });
    </script>
</body>
</html>
