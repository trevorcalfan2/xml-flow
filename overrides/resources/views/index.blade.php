<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>XML Flow — Gestión de Comprobantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #0f2744;
            --brand-mid: #1a3a5c;
            --accent: #0d6efd;
        }
        * { box-sizing: border-box; }
        body { background: #eef1f6; font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; }

        /* Navbar */
        .topbar { background: var(--brand); padding: 0 1.5rem; height: 56px; display: flex; align-items: center; gap: 1rem; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 12px rgba(0,0,0,.3); }
        .topbar-logo { color: #fff; font-weight: 700; font-size: 1.1rem; letter-spacing: -.3px; display: flex; align-items: center; gap: .5rem; }
        .topbar-sub { color: rgba(255,255,255,.45); font-size: .8rem; margin-left: auto; }

        /* Cards */
        .card { border: none; border-radius: 14px; box-shadow: 0 2px 16px rgba(0,0,0,.07); }
        .card-header { border-radius: 14px 14px 0 0 !important; border-bottom: none; padding: .85rem 1.25rem; }

        /* Upload */
        .drop-zone { border: 2px dashed #c5cdd8; border-radius: 12px; padding: 2.5rem 1rem; text-align: center; cursor: pointer; transition: all .2s; background: #fff; }
        .drop-zone:hover, .drop-zone.over { border-color: var(--accent); background: #eef4ff; }
        .drop-zone .icon { font-size: 3rem; color: #b0bac8; }

        /* Steps */
        .step-row { display: flex; align-items: center; gap: 12px; padding: 9px 0; border-bottom: 1px solid #f3f4f6; }
        .step-row:last-child { border-bottom: none; }
        .step-dot { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }

        /* Stat cards */
        .stat { border-radius: 12px; padding: 1rem 1.25rem; color: #fff; }
        .stat .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .6px; opacity: .75; margin-bottom: .2rem; }
        .stat .val { font-size: 1.35rem; font-weight: 700; }
        .stat-blue  { background: linear-gradient(135deg, #0f2744, #1e5799); }
        .stat-green { background: linear-gradient(135deg, #0f4c2a, #198754); }
        .stat-purple{ background: linear-gradient(135deg, #3b0764, #7c3aed); }

        /* Table */
        .tbl th { font-size: 11px; text-transform: uppercase; letter-spacing: .4px; color: #8892a0; font-weight: 600; padding: .5rem .75rem; }
        .tbl td { font-size: 13px; padding: .55rem .75rem; vertical-align: middle; }

        /* XML preview */
        .xml-box { font-family: 'Consolas', 'Courier New', monospace; font-size: 11px; background: #0d1117; color: #79c0ff; border-radius: 8px; padding: .85rem 1rem; max-height: 180px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; cursor: pointer; border: 1px solid #30363d; }
        .xml-box:hover { border-color: var(--accent); }

        /* Emission panel */
        .emit-panel { position: sticky; top: 72px; }
        .field-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; color: #5a6474; margin-bottom: .3rem; display: flex; align-items: center; gap: .4rem; }
        .field-badge { background: #e9ecef; color: #495057; font-size: 10px; padding: 1px 7px; border-radius: 4px; font-family: monospace; font-weight: 600; }
        .emit-panel input[readonly], .emit-panel textarea[readonly] { background: #f8f9fc; font-family: 'Consolas', monospace; font-size: 12px; border-color: #dee2e6; }
        .dest-tag { font-size: 11px; color: #6c757d; }
        .dest-tag code { background: #f0f0f0; padding: 1px 6px; border-radius: 4px; font-size: 11px; }

        /* Progress */
        .progress { height: 6px; border-radius: 3px; }
        #emit-pbar { transition: width .3s ease; }
        #proc-pbar { transition: width .4s ease; }

        /* Response */
        .resp-box { font-family: 'Consolas', monospace; font-size: 12px; background: #0d1117; color: #3fb950; border-radius: 8px; padding: .85rem 1rem; max-height: 250px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; display: none; border: 1px solid #30363d; }
        .resp-box.err { color: #ff7b72; }

        /* Section headers */
        .sec-hdr { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }

        /* Logistics grid */
        .log-item .lk { font-size: 11px; color: #8892a0; margin-bottom: 1px; }
        .log-item .lv { font-size: 13px; font-weight: 600; color: #1a1a2e; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c5cdd8; border-radius: 3px; }

        /* XML Tree */
        .xml-tree { font-family: 'Consolas','Courier New',monospace; font-size: 12px; background: #0d1117; color: #cdd6f4; border-radius: 8px; padding: 1rem; max-height: 420px; overflow-y: auto; border: 1px solid #30363d; line-height: 1.7; }
        .xml-node { margin-left: 18px; }
        .xml-leaf { margin-left: 18px; }
        .xml-toggle { cursor: pointer; user-select: none; display: inline-flex; align-items: center; gap: 4px; }
        .xml-toggle:hover .xt-tag { text-decoration: underline; }
        .toggle-icon { font-size: 10px; color: #6e738d; width: 12px; display: inline-block; transition: transform .15s; }
        .toggle-icon.closed { transform: rotate(-90deg); }
        .xml-children { margin-left: 18px; border-left: 1px dashed #30363d; padding-left: 8px; }
        .xt-tag  { color: #89b4fa; }
        .xt-text { color: #a6e3a1; }
        .xt-attr { color: #f9e2af; }
        .xt-empty { color: #6e738d; font-style: italic; }

        /* Pretty view */
        .xml-pretty { font-family: 'Consolas','Courier New',monospace; font-size: 12px; background: #0d1117; color: #79c0ff; border-radius: 8px; padding: 1rem; max-height: 420px; overflow-y: auto; white-space: pre; border: 1px solid #30363d; line-height: 1.6; }
    </style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <div class="topbar-logo">
        <i class="bi bi-file-earmark-code-fill" style="color:#4da3ff"></i>
        XML Flow
    </div>
    <span style="color:rgba(255,255,255,.3); font-size:1.2rem">|</span>
    <span style="color:rgba(255,255,255,.6); font-size:.85rem">Gestión de Comprobantes Electrónicos</span>
    <span class="topbar-sub">ADUAMERICA SOLUCIONES LOGÍSTICAS SA</span>
    <button class="ms-auto btn btn-outline-light btn-sm" style="font-size:12px;opacity:.75;"
            type="button" onclick="toggleHistorial()">
        <i class="bi bi-clock-history me-1"></i>Historial
    </button>
</div>

<div class="container-xl py-4 px-3 px-md-4">
    @include('partials.upload')
    <div id="steps-list" style="display:none"></div>
    @include('partials.results')
</div>

@include('partials.modals')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.scripts')
</body>
</html>
