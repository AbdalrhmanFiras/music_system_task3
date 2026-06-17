<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>

<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Test Page</h1>
            <p class="subtitle">This is a simple HTML test page for your Laravel app.</p>

            <div class="box">
                <h2 class="subtitle">API Links</h2>
                <ul>
                    <li><a href="/docs/api" target="_blank">API Docs (Scramble UI)</a></li>
                    <li><a href="/docs/api.json" target="_blank">API JSON Spec</a></li>
                    <li><a href="/api/artist" target="_blank">Artists (API)</a></li>
                </ul>
            </div>

            <div class="box">
                <h2 class="subtitle">Quick Actions</h2>
                <button class="button is-primary"
                    onclick="fetch('/api/auth/me').then(r=>r.json()).then(d=>alert(JSON.stringify(d))).catch(e=>alert('Not authenticated'))">Call
                    /api/auth/me</button>
            </div>
        </div>
    </section>
</body>

</html>
