<h1>Codigo de verificacion</h1>

<p>Hola {{ $user->name }},</p>

<p>Tu codigo de verificacion es: <strong>{{ $code }}</strong></p>

<p>Este codigo vence en {{ config('auth.verification_code_ttl', 10) }} minutos.</p>
