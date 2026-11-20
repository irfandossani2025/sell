<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Sign in | Hot Products</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="admin-auth-wrap">
    <section class="admin-auth-art"><a class="brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true"><span></span><span></span><span></span></span><span>hot<span class="brand-light">products</span></span></a><div class="admin-auth-art-copy"><span class="eyebrow">YOUR SHORTLIST, YOUR WAY</span><h1>Good things<br>start here.</h1><p>Manage your finds, keep an eye on the numbers, and make this space your own.</p></div><span class="footer-label">HOT PRODUCTS · PRIVATE ADMIN</span></section>
    <section class="admin-auth-panel"><form class="admin-auth-form" method="post" action="{{ route('admin.login.submit') }}">@csrf<span class="eyebrow">WELCOME BACK</span><h2>Sign in.</h2><p>Your one-person workspace is waiting.</p>@if ($errors->any())<div class="form-error" role="alert">{{ $errors->first() }}</div>@endif
        <label>Email address<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
        <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
        <label class="admin-form-check"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
        <button class="button button-dark" type="submit">Sign in <span aria-hidden="true">↗</span></button><small class="admin-auth-help">Admin access is created securely from your hosting control panel.</small>
    </form></section>
</body>
</html>
