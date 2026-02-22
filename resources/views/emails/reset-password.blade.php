<x-mail::message>
# Merhaba!

Şifre sıfırlama talebinde bulundunuz. <br>

Aşağıdaki butona tıklayarak yeni şifrenizi belirleyebilirsiniz. Bu link {{ $expireMinutes }} dakika geçerlidir.

<x-mail::button :url="$url" color="primary">
Şifreyi Sıfırla
</x-mail::button>

Teşekkürler,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Butona tıklayamıyorsanız aşağıdaki adresi tarayıcınıza kopyalayın: [{{ $url }}]({{ $url }})
</x-mail::subcopy>
</x-mail::message>
