<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>XML Flow — Historial de Emisiones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background:#0a0d12; color:#c9d1d9; font-family:'Segoe UI',system-ui,sans-serif; }
        .card-dark { background:#0d1117; border:1px solid #21262d; border-radius:10px; }
        .table-dark-custom { background:#0d1117; color:#c9d1d9; }
        .table-dark-custom thead th { background:#161b22; color:#8b949e; border-color:#21262d; font-size:11px; text-transform:uppercase; letter-spacing:.5px; }
        .table-dark-custom tbody td { border-color:#21262d; vertical-align:middle; font-size:13px; }
        .table-dark-custom tbody tr:hover { background:#161b22; }
        .badge-ok { background:#1a7f37; color:#fff; }
        .badge-err { background:#b91c1c; color:#fff; }
        .stat-card { background:#0d1117; border:1px solid #21262d; border-radius:10px; padding:1.2rem; }
        .stat-val { font-size:2rem; font-weight:700; line-height:1; }
        .navbar-custom { background:#161b22; border-bottom:1px solid #21262d; }
        pre.resp { font-size:10px; white-space:pre-wrap; word-break:break-all; max-height:300px; overflow-y:auto; background:#0a0d12; color:#79c0ff; padding:1rem; border-radius:6px; }
        .search-box { background:#161b22; border:1px solid #30363d; color:#c9d1d9; border-radius:6px; padding:.4rem .75rem; width:260px; }
        .search-box:focus { outline:none; border-color:#58a6ff; box-shadow:0 0 0 2px rgba(88,166,255,.15); }
        .search-box::placeholder { color:#6e7681; }
    </style>
</head>
<body>
<nav class="navbar navbar-custom px-4 py-3 mb-4">
    <a href="/" class="text-decoration-none d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left text-secondary"></i>
        <span class="fw-semibold text-white" style="font-size:15px;">XML Flow</span>
        <span class="text-secondary mx-1">/</span>
        <span class="text-secondary" style="font-size:14px;">Historial de Emisiones</span>
    </a>
</nav>

<div class="container-xl pb-5">

    {{-- Stats --}}
    @php
        $total   = $emissions->count();
        $ok      = $emissions->where('status','OK')->count();
        $err     = $emissions->where('status','!=','OK')->count();
        $sumTotal = $emissions->sum('total');
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="text-muted small mb-1">Total Emisiones</div>
                <div class="stat-val text-white">{{ $total }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="text-muted small mb-1">Exitosas</div>
                <div class="stat-val text-success">{{ $ok }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="text-muted small mb-1">Con Error</div>
                <div class="stat-val text-danger">{{ $err }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card text-center">
                <div class="text-muted small mb-1">Total Facturado</div>
                <div class="stat-val text-info" style="font-size:1.4rem;">S/ {{ number_format($sumTotal, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card-dark p-0 overflow-hidden">
        <div class="d-flex align-items-center justify-content-between p-3" style="border-bottom:1px solid #21262d;">
            <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-clock-history me-2 text-info"></i>Registro de Emisiones</h6>
            <input type="text" class="search-box" id="search-input" placeholder="Buscar por doc, fecha..." onkeyup="filterTable()">
        </div>
        <div class="table-responsive">
            <table class="table table-dark-custom mb-0" id="emissions-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha Emisión</th>
                        <th>Documento</th>
                        <th>Fecha Comp.</th>
                        <th>Total</th>
                        <th>IGV</th>
                        <th>Estado</th>
                        <th>CDR</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($emissions as $e)
                    <tr>
                        <td class="text-muted">{{ $e->id }}</td>
                        <td>{{ $e->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="fw-semibold text-white">{{ $e->serie }}-{{ $e->numero }}</span>
                            @if($e->tipo)
                                <span class="badge ms-1" style="background:#1f2937;color:#9ca3af;font-size:9px;">T{{ $e->tipo }}</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $e->fecha_emision ?? '—' }}</td>
                        <td class="text-success fw-semibold">{{ $e->total ? 'S/ '.number_format($e->total,2) : '—' }}</td>
                        <td class="text-info">{{ $e->igv ? 'S/ '.number_format($e->igv,2) : '—' }}</td>
                        <td>
                            <span class="badge {{ $e->status === 'OK' ? 'badge-ok' : 'badge-err' }}" style="font-size:11px;">
                                {{ $e->status }}
                            </span>
                        </td>
                        <td>
                            @if($e->url_acepta && str_starts_with($e->url_acepta,'http'))
                                <a href="{{ $e->url_acepta }}" target="_blank" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size:11px;">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Ver CDR
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:11px;"
                                onclick="showDetail({{ $e->id }})">
                                <i class="bi bi-eye me-1"></i>Detalle
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">Sin emisiones registradas</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detail-modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="background:#0d1117;border:1px solid #21262d;">
            <div class="modal-header" style="border-color:#21262d;">
                <h6 class="modal-title text-white mb-0"><i class="bi bi-file-text me-2 text-info"></i>Detalle de Emisión #<span id="d-id"></span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="detail-tabs">
                    <li class="nav-item"><a class="nav-link active" href="#" onclick="showTab('tab-resp');return false;">Respuesta</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" onclick="showTab('tab-xml');return false;">XML Enviado</a></li>
                </ul>
                <div id="tab-resp"><pre class="resp" id="d-resp"></pre></div>
                <div id="tab-xml" style="display:none"><pre class="resp" id="d-xml"></pre></div>
            </div>
            <div class="modal-footer" style="border-color:#21262d;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const emissionsData = @json($emissions->keyBy('id'));
const detailModal   = new bootstrap.Modal(document.getElementById('detail-modal'));

function showDetail(id) {
    const e = emissionsData[id];
    if (!e) return;
    document.getElementById('d-id').textContent   = id;
    document.getElementById('d-resp').textContent = e.respuesta_raw || '(sin respuesta)';
    document.getElementById('d-xml').textContent  = e.xml_enviado  || '(sin XML)';
    showTab('tab-resp');
    detailModal.show();
}

function showTab(active) {
    ['tab-resp','tab-xml'].forEach(t => {
        document.getElementById(t).style.display = t === active ? '' : 'none';
    });
    document.querySelectorAll('#detail-tabs .nav-link').forEach((a,i) => {
        a.classList.toggle('active', (i === 0 && active === 'tab-resp') || (i === 1 && active === 'tab-xml'));
    });
}

function filterTable() {
    const q = document.getElementById('search-input').value.toLowerCase();
    document.querySelectorAll('#emissions-table tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
</body>
</html>
