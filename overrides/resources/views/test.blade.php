<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Conexión ca4xml</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container" style="max-width:700px">
    <h5 class="mb-3"><span class="badge bg-warning text-dark me-2">TEST</span>Conexión a ca4xml</h5>

    <div class="card mb-3">
        <div class="card-body">
            <div class="mb-2">
                <label class="form-label small fw-bold">comando</label>
                <input type="text" id="t-comando" class="form-control form-control-sm" value="emitir">
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">docid</label>
                <input type="text" id="t-docid" class="form-control form-control-sm" value="F001-00000001">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">datos (XML)</label>
                <textarea id="t-datos" class="form-control form-control-sm" rows="3" style="font-family:monospace;font-size:11px;"><?xml version="1.0" encoding="ISO-8859-1"?><test/></textarea>
            </div>
            <button class="btn btn-primary btn-sm" onclick="runTest()">Probar conexión</button>
        </div>
    </div>

    <div id="result" style="display:none">
        <div id="status-badge" class="mb-2"></div>
        <pre id="log-box" style="background:#0d1117;color:#3fb950;padding:1rem;border-radius:8px;font-size:12px;white-space:pre-wrap;word-break:break-all;max-height:400px;overflow-y:auto;"></pre>
    </div>
</div>

<script>
async function runTest() {
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Probando...';
    document.getElementById('result').style.display = 'none';

    // Refresh CSRF
    try {
        const cr = await fetch('/csrf-token');
        const cj = await cr.json();
        document.querySelector('meta[name=csrf-token]').setAttribute('content', cj.token);
    } catch(_) {}

    const token = document.querySelector('meta[name=csrf-token]').content;

    try {
        const r = await fetch('/test-connection', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({
                comando: document.getElementById('t-comando').value,
                docid:   document.getElementById('t-docid').value,
                datos:   document.getElementById('t-datos').value,
            }),
        });
        const j = await r.json();

        document.getElementById('result').style.display = '';
        document.getElementById('status-badge').innerHTML = j.ok
            ? '<span class="badge bg-success fs-6">OK — HTTP ' + j.status + '</span>'
            : '<span class="badge bg-danger fs-6">ERROR — ' + (j.error || 'desconocido') + '</span>';

        document.getElementById('log-box').style.color = j.ok ? '#3fb950' : '#ff7b72';
        document.getElementById('log-box').textContent = (j.log || []).join('\n')
            + (j.body ? '\n\nRESPUESTA:\n' + j.body : '')
            + (j.error ? '\n\nERROR:\n' + j.error + '\n' + (j.class || '') : '');
    } catch(e) {
        document.getElementById('result').style.display = '';
        document.getElementById('status-badge').innerHTML = '<span class="badge bg-danger fs-6">ERROR JS: ' + e.message + '</span>';
        document.getElementById('log-box').style.color = '#ff7b72';
        document.getElementById('log-box').textContent = e.message;
    } finally {
        btn.disabled = false;
        btn.textContent = 'Probar conexión';
    }
}
</script>
</body>
</html>
