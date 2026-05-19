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

    <!-- ══ UPLOAD ══ -->
    <div id="sec-upload">
        <div class="d-flex gap-3 align-items-stretch" id="upload-row">
            <div id="upload-col" style="flex:1 1 auto;min-width:320px;max-width:1400px;transition:max-width .4s ease;position:relative;z-index:1;">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <span class="fw-semibold"><i class="bi bi-upload me-2 text-primary"></i>Cargar XML de Comprobante</span>
                    </div>
                    <div class="card-body">
                        <div class="drop-zone" id="drop-zone">
                            <div class="icon"><i class="bi bi-file-earmark-code"></i></div>
                            <p class="fw-semibold text-secondary mt-3 mb-1">Arrastra el archivo XML aquí</p>
                            <p class="text-muted small mb-3">Soporta ISO-8859-1 y UTF-8 — el sistema corrige automáticamente</p>
                            <input type="file" id="file-inp" accept=".xml" class="d-none">
                            <button class="btn btn-outline-primary btn-sm" onclick="document.getElementById('file-inp').click(); event.stopPropagation();">
                                <i class="bi bi-folder2-open me-1"></i>Seleccionar archivo
                            </button>
                        </div>

                        <div id="file-chip" class="mt-2" style="display:none">
                            <span class="badge bg-primary bg-opacity-10 text-primary py-1 px-2">
                                <i class="bi bi-file-check me-1"></i><span id="file-name"></span>
                                <button class="btn btn-link btn-sm p-0 ms-1 text-danger" onclick="clearFile()" style="font-size:12px">×</button>
                            </span>
                        </div>

                        <div class="text-center text-muted my-3" style="font-size:.8rem">— o pega el contenido XML —</div>

                        <textarea id="xml-paste" class="form-control" rows="5"
                            placeholder="Pega el contenido XML aquí..."
                            style="font-family:monospace;font-size:12px;"></textarea>

                        <div id="proc-status" style="display:none" class="mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <small class="text-muted" id="proc-title">Procesando...</small>
                                <div id="proc-spin" class="spinner-border spinner-border-sm text-primary"></div>
                            </div>
                            <div class="progress bg-light" style="height:5px;">
                                <div class="progress-bar bg-primary" id="proc-pbar" style="width:0%"></div>
                            </div>
                        </div>
                        <div id="proc-error" class="alert alert-danger py-2 mt-2 small" style="display:none"></div>
                        <button class="btn btn-primary w-100 mt-3" id="proc-btn" onclick="processXml()">
                            <i class="bi bi-magic me-2"></i>Corregir y Procesar XML
                        </button>
                    </div>
                </div>
            </div>

            <!-- Historial panel -->
            <div id="historial-col" style="flex:1 1 0;min-width:0;overflow:hidden;max-width:0;transition:max-width .4s ease;position:relative;z-index:1;">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <span class="fw-semibold"><i class="bi bi-clock-history me-2 text-primary"></i>Historial de Emisiones</span>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="d-flex gap-2 text-center" style="font-size:11px;">
                                <span class="text-muted">Total: <strong id="h-stat-total" class="text-dark">—</strong></span>
                                <span class="text-muted">|</span>
                                <span class="text-muted">OK: <strong id="h-stat-ok" class="text-success">—</strong></span>
                            </div>
                            <input type="text" id="h-search" placeholder="Buscar..." onkeyup="filterHistorial()"
                                   class="form-control form-control-sm" style="width:150px;">
                            <button class="btn btn-outline-secondary btn-sm py-0" onclick="loadHistorial(true)" title="Recargar">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0" style="overflow:hidden;display:flex;flex-direction:column;">
                        <div class="table-responsive" style="overflow-y:auto;flex:1;">
                            <table class="table table-sm table-hover mb-0" id="h-table" style="font-size:12px;">
                                <thead class="table-light sticky-top">
                                    <tr style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#6c757d;">
                                        <th>#</th><th>Fecha</th><th>Documento</th>
                                        <th>Tipo</th><th>Estado</th><th>CDR</th><th></th>
                                    </tr>
                                </thead>
                                <tbody id="h-tbody">
                                    <tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /upload -->

    <div id="steps-list" style="display:none"></div>

    <!-- ══ RESULTS ══ -->
    <div id="sec-results" class="mt-4" style="display:none">
        <div class="row g-4">

            <!-- LEFT: invoice data -->
            <div class="col-lg-7">

                <!-- Invoice header card -->
                <div class="card mb-4">
                    <div class="card-header" style="background:var(--brand);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="badge bg-warning text-dark me-2" id="d-type-badge"></span>
                                <span class="text-white fw-bold fs-5" id="d-inv-id"></span>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-info bg-opacity-20 text-white border border-info border-opacity-25" id="d-currency"></span>
                                <span class="badge bg-light text-dark" id="d-order-ref-badge"></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="text-muted small">Emisión</div>
                                <strong id="d-issue-date"></strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small">Hora</div>
                                <strong id="d-issue-time"></strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small">Vencimiento</div>
                                <strong id="d-due-date"></strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-muted small">O/S</div>
                                <strong id="d-order-ref"></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="row g-3 mb-4">
                    <div class="col-4"><div class="stat stat-blue"><div class="lbl">Subtotal</div><div class="val" id="d-subtotal"></div></div></div>
                    <div class="col-4"><div class="stat stat-green"><div class="lbl">IGV 18%</div><div class="val" id="d-tax"></div></div></div>
                    <div class="col-4"><div class="stat stat-purple"><div class="lbl">Total</div><div class="val" id="d-total"></div></div></div>
                </div>

                <div class="alert alert-secondary py-2 px-3 mb-4" style="font-size:13px; border-left: 3px solid var(--accent);">
                    <i class="bi bi-chat-quote me-1 text-primary"></i>
                    <span id="d-amount-words"></span>
                </div>

                <!-- Parties -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light py-2">
                                <span class="sec-hdr"><i class="bi bi-building me-1"></i>Emisor</span>
                            </div>
                            <div class="card-body py-2">
                                <div class="fw-semibold mb-1" style="font-size:13px" id="d-sup-name"></div>
                                <div class="text-muted small mb-1">RUC <strong id="d-sup-ruc"></strong></div>
                                <div class="text-muted small mb-1" id="d-sup-addr"></div>
                                <div class="text-muted small mb-1"><i class="bi bi-envelope me-1"></i><span id="d-sup-email"></span></div>
                                <div class="text-muted small"><i class="bi bi-telephone me-1"></i><span id="d-sup-phone"></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light py-2">
                                <span class="sec-hdr"><i class="bi bi-person-fill me-1"></i>Receptor</span>
                            </div>
                            <div class="card-body py-2">
                                <div class="fw-semibold mb-1" style="font-size:13px" id="d-cus-name"></div>
                                <div class="text-muted small mb-1">RUC <strong id="d-cus-ruc"></strong></div>
                                <div class="text-muted small" id="d-cus-addr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line items -->
                <div class="card mb-4">
                    <div class="card-header bg-light py-2">
                        <span class="sec-hdr"><i class="bi bi-list-ul me-1"></i>Ítems</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table tbl table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th><th>Descripción</th><th>Código</th>
                                        <th class="text-end">P.Unit</th>
                                        <th class="text-end">C/IGV</th>
                                        <th class="text-end">IGV</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="d-lines"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payments -->
                <div class="card mb-4">
                    <div class="card-header bg-light py-2">
                        <span class="sec-hdr"><i class="bi bi-credit-card me-1"></i>Condiciones de Pago</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table tbl mb-0">
                            <thead class="table-light">
                                <tr><th>Medio</th><th class="text-end">Monto</th><th>Vencimiento</th></tr>
                            </thead>
                            <tbody id="d-payments"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Logistics -->
                <div class="card mb-4">
                    <div class="card-header bg-light py-2">
                        <span class="sec-hdr"><i class="bi bi-ship me-1"></i>Información Logística</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="d-logistics"></div>
                    </div>
                </div>

                <!-- Banks -->
                <div class="card mb-4">
                    <div class="card-header bg-light py-2">
                        <span class="sec-hdr"><i class="bi bi-bank me-1"></i>Cuentas Bancarias</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table tbl mb-0">
                                <thead class="table-light">
                                    <tr><th>Banco</th><th>Moneda</th><th>Cuenta</th><th>CCI</th></tr>
                                </thead>
                                <tbody id="d-banks"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Leyendas -->
                <div class="card mb-4">
                    <div class="card-body" style="font-size:12px; color:#555;">
                        <p class="mb-1"><i class="bi bi-info-circle me-1 text-warning"></i><span id="d-leyenda2"></span></p>
                        <p class="mb-0"><i class="bi bi-piggy-bank me-1 text-success"></i><span id="d-leyenda3"></span></p>
                    </div>
                </div>

                <!-- Reset -->
                <button class="btn btn-outline-secondary btn-sm mb-4" onclick="resetApp()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Procesar otro XML
                </button>

            </div><!-- /col-lg-7 -->

            <!-- RIGHT: emission panel -->
            <div class="col-lg-5">
                <div class="emit-panel">
                    <div class="card">
                        <div class="card-header" style="background:#0f5132;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white fw-semibold"><i class="bi bi-send-fill me-2"></i>Emisión al Sistema</span>
                                <span class="badge bg-success bg-opacity-50 text-white" style="font-size:10px">ca4xml</span>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- XML fixed preview -->
                            <div class="mb-3">
                                <div class="field-label">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    XML Corregido
                                    <span class="ms-auto text-muted" style="font-size:10px;text-transform:none;letter-spacing:0">clic para ver completo</span>
                                </div>
                                <div class="xml-box" id="xml-preview" onclick="openXmlModal()" title="Ver XML completo">
                                </div>
                            </div>

                            <hr class="my-3">

                            <!-- datos -->
                            <div class="mb-3">
                                <div class="field-label">
                                    <span class="field-badge">datos</span>
                                    Contenido XML a enviar
                                </div>
                                <textarea id="f-datos" class="form-control" rows="5" readonly
                                    style="font-size:11px;font-family:monospace;resize:none;"></textarea>
                            </div>

                            <!-- comando -->
                            <div class="mb-3">
                                <div class="field-label">
                                    <span class="field-badge">comando</span>
                                    Acción
                                </div>
                                <input type="text" id="f-comando" class="form-control form-control-sm" value="emitir" readonly>
                            </div>

                            <!-- docid -->
                            <div class="mb-3">
                                <div class="field-label">
                                    <span class="field-badge">docid</span>
                                    Identificador del Documento
                                </div>
                                <input type="text" id="f-docid" class="form-control form-control-sm fw-bold" readonly>
                            </div>

                            <div class="dest-tag mb-3">
                                <i class="bi bi-arrow-right-circle me-1"></i>
                                POST → <code>http://192.168.1.149:5002/ca4xml</code>
                            </div>

                            <!-- preview button -->
                            <button class="btn btn-outline-primary w-100 mb-3" onclick="openPreview()">
                                <i class="bi bi-eye me-2"></i>Vista Previa del Envío
                            </button>

                            <!-- progress -->
                            <div id="emit-progress-wrap" style="display:none" class="mb-3">
                                <div class="d-flex justify-content-between mb-1" style="font-size:12px">
                                    <span class="text-muted" id="emit-status-txt">Conectando...</span>
                                    <span id="emit-pct-txt">0%</span>
                                </div>
                                <div class="progress bg-light">
                                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="emit-pbar" style="width:0%"></div>
                                </div>
                            </div>

                            <!-- button -->
                            <button class="btn btn-success w-100 fw-semibold" id="emit-btn" onclick="emitir()">
                                <i class="bi bi-send me-2"></i>Emitir Comprobante
                            </button>

                            <!-- response -->
                            <div id="resp-box" class="resp-box mt-3"></div>

                        </div>
                    </div>
                </div>
            </div><!-- /col-lg-5 -->

        </div><!-- /row -->
    </div><!-- /results -->

</div><!-- /container -->


<!-- Preview Send Modal -->
<div class="modal fade" id="preview-modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--brand);">
                <h5 class="modal-title text-white mb-0">
                    <i class="bi bi-send-check me-2"></i>Vista Previa — Datos a Enviar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Field summary -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded border bg-light">
                            <div class="field-label mb-1"><span class="field-badge">comando</span></div>
                            <div class="fw-bold fs-5" id="pv-comando"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded border bg-light">
                            <div class="field-label mb-1"><span class="field-badge">docid</span></div>
                            <div class="fw-bold fs-5" id="pv-docid"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded border bg-light">
                            <div class="field-label mb-1"><i class="bi bi-arrow-right-circle me-1 text-success"></i>Destino</div>
                            <code>http://192.168.1.149:5002/ca4xml</code>
                            <span class="badge bg-success ms-2">POST</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                    <div class="field-label mb-0"><span class="field-badge">datos</span> — XML Corregido Completo</div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-secondary active" id="view-btn-raw"    onclick="setXmlView('raw',    this)"><i class="bi bi-code me-1"></i>Raw</button>
                        <button class="btn btn-outline-secondary"        id="view-btn-pretty" onclick="setXmlView('pretty', this)"><i class="bi bi-indent me-1"></i>Beautified</button>
                        <button class="btn btn-outline-secondary"        id="view-btn-tree"   onclick="setXmlView('tree',   this)"><i class="bi bi-diagram-3 me-1"></i>Árbol</button>
                    </div>
                </div>

                <!-- Raw view -->
                <pre id="pv-raw" style="font-family:'Consolas','Courier New',monospace;font-size:12px;background:#0d1117;color:#79c0ff;padding:1rem;border-radius:8px;white-space:pre-wrap;word-break:break-all;max-height:420px;overflow-y:auto;border:1px solid #30363d;line-height:1.6;"></pre>

                <!-- Beautified view -->
                <div id="pv-pretty" class="xml-pretty" style="display:none"></div>

                <!-- Tree view -->
                <div id="pv-tree" class="xml-tree" style="display:none"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-info btn-sm" onclick="downloadXml()">
                    <i class="bi bi-download me-1"></i>Descargar XML
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="copyXmlFixed(this)">
                    <i class="bi bi-clipboard me-1"></i>Copiar XML
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-success btn-sm" data-bs-dismiss="modal" onclick="emitir()">
                    <i class="bi bi-send me-1"></i>Confirmar y Emitir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Historial Detail Modal -->
<div class="modal fade" id="detail-modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title mb-0"><i class="bi bi-file-text me-2 text-primary"></i>Detalle emisión #<span id="d-id"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="detail-tabs">
                    <li class="nav-item"><a class="nav-link active" href="#" onclick="showTab('tab-resp');return false;">Respuesta</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" onclick="showTab('tab-xml');return false;">XML Enviado</a></li>
                </ul>
                <div id="tab-resp"><pre style="font-size:10px;white-space:pre-wrap;word-break:break-all;max-height:400px;overflow-y:auto;background:#f8f9fa;color:#212529;padding:1rem;border-radius:6px;border:1px solid #dee2e6;" id="d-resp"></pre></div>
                <div id="tab-xml" style="display:none"><pre style="font-size:10px;white-space:pre-wrap;word-break:break-all;max-height:400px;overflow-y:auto;background:#f8f9fa;color:#212529;padding:1rem;border-radius:6px;border:1px solid #dee2e6;" id="d-xml"></pre></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Emission Result Modal -->
<div class="modal fade" id="emit-modal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-success border-opacity-50">
                <h5 class="modal-title mb-0">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Comprobante Emitido Exitosamente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="p-3 rounded text-center bg-light border">
                            <div class="text-muted small">Serie-Número</div>
                            <div class="fw-bold fs-5" id="er-docid">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded text-center bg-light border">
                            <div class="text-muted small">Fecha Emisión</div>
                            <div class="fw-bold fs-5" id="er-fecha">—</div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small mb-1 d-block"><i class="bi bi-link-45deg me-1"></i>Enlace CDR (Acepta)</label>
                    <a id="er-url" href="#" target="_blank" class="btn btn-outline-success w-100 text-start text-truncate" style="font-size:13px;">
                        <i class="bi bi-box-arrow-up-right me-2"></i><span id="er-url-txt">—</span>
                    </a>
                </div>
                <div class="mb-2">
                    <label class="text-muted small mb-1 d-block"><i class="bi bi-shield-check me-1"></i>Hash</label>
                    <code class="d-block p-2 rounded bg-light border" style="font-size:11px;word-break:break-all;color:#0d6efd;" id="er-hash">—</code>
                </div>
                <details class="mt-2">
                    <summary class="text-muted small" style="cursor:pointer;">Ver firma digital</summary>
                    <pre class="mt-2 p-2 rounded bg-light border" style="font-size:10px;white-space:pre-wrap;word-break:break-all;max-height:120px;overflow-y:auto;" id="er-firma"></pre>
                </details>
                <details class="mt-2">
                    <summary class="text-muted small" style="cursor:pointer;">Ver respuesta raw</summary>
                    <pre class="mt-2 p-2 rounded bg-light border" style="font-size:10px;white-space:pre-wrap;word-break:break-all;max-height:120px;overflow-y:auto;" id="er-raw"></pre>
                </details>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- XML Full Modal -->
<div class="modal fade" id="xml-modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="background:#0d1117;">
            <div class="modal-header" style="border-color:#30363d;">
                <h6 class="modal-title text-white mb-0"><i class="bi bi-file-earmark-code me-2 text-info"></i>XML Corregido — Vista Completa</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <pre id="xml-modal-body" style="font-family:'Consolas','Courier New',monospace;font-size:12px;color:#79c0ff;padding:1.25rem;margin:0;white-space:pre-wrap;word-break:break-all;line-height:1.6;"></pre>
            </div>
            <div class="modal-footer" style="border-color:#30363d;">
                <button class="btn btn-outline-info btn-sm" onclick="copyXml(this)">
                    <i class="bi bi-clipboard me-1"></i>Copiar XML
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentFile = null;
let fixedXml = '';
let bsXmlModal     = null;
let bsPreviewModal = null;
let bsEmitModal    = null;
let bsDetailModal  = null;

document.addEventListener('DOMContentLoaded', () => {
    bsXmlModal     = new bootstrap.Modal(document.getElementById('xml-modal'));
    bsPreviewModal = new bootstrap.Modal(document.getElementById('preview-modal'));
    bsEmitModal    = new bootstrap.Modal(document.getElementById('emit-modal'));
    bsDetailModal  = new bootstrap.Modal(document.getElementById('detail-modal'));
    initDropZone();
});

function showTab(active) {
    ['tab-resp','tab-xml'].forEach(t => {
        document.getElementById(t).style.display = t === active ? '' : 'none';
    });
    document.querySelectorAll('#detail-tabs .nav-link').forEach((a,i) => {
        a.classList.toggle('active', (i === 0 && active === 'tab-resp') || (i === 1 && active === 'tab-xml'));
    });
}

/* ─── Drop Zone ─── */
function initDropZone() {
    const zone = document.getElementById('drop-zone');
    const inp  = document.getElementById('file-inp');

    zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('over'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('over');
        const f = e.dataTransfer.files[0];
        if (f && f.name.endsWith('.xml')) setFile(f);
    });
    zone.addEventListener('click', e => {
        if (!e.target.closest('button')) inp.click();
    });
    inp.addEventListener('change', () => inp.files[0] && setFile(inp.files[0]));
}

function setFile(f) {
    currentFile = f;
    document.getElementById('file-name').textContent = f.name;
    document.getElementById('file-chip').style.display = '';
    document.getElementById('xml-paste').value = '';
}

function clearFile() {
    currentFile = null;
    document.getElementById('file-chip').style.display = 'none';
    document.getElementById('file-inp').value = '';
}

/* ─── Process ─── */
async function processXml() {
    const paste = document.getElementById('xml-paste').value.trim();
    if (!currentFile && !paste) {
        alert('Selecciona un archivo XML o pega el contenido.');
        return;
    }

    const btn = document.getElementById('proc-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

    show('proc-status');
    hide('sec-results');
    document.getElementById('proc-error').style.display = 'none';
    document.getElementById('proc-title').textContent = 'Procesando XML...';
    document.getElementById('proc-spin').className = 'spinner-border spinner-border-sm text-primary';
    setBar('proc-pbar', 0, 0); setBar('proc-pbar', 50, 1.2);

    const fd = new FormData();
    fd.append('_token', csrfToken());
    if (currentFile) {
        fd.append('xml_file', currentFile);
    } else {
        fd.append('xml_content', paste);
    }

    try {
        const r    = await fetch('/process', { method: 'POST', body: fd });
        const json = await r.json();

        setBar('proc-pbar', 100, 0.4);
        await sleep(500);

        renderSteps(json.steps || []);

        if (json.error) {
            document.getElementById('proc-title').textContent = 'Error al procesar';
            document.getElementById('proc-spin').className = 'bi bi-x-circle-fill text-danger fs-5';
            const errEl = document.getElementById('proc-error');
            errEl.textContent = json.error;
            errEl.style.display = '';
            return;
        }

        fixedXml = json.fixed_xml || '';
        document.getElementById('proc-title').textContent = 'XML procesado correctamente';
        document.getElementById('proc-spin').className = 'bi bi-check-circle-fill text-success fs-5';

        await sleep(400);
        renderResults(json.data, json.doc_id, fixedXml);
        show('sec-results');
        document.getElementById('sec-results').scrollIntoView({ behavior: 'smooth' });

    } catch (e) {
        alert('Error de conexión: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-magic me-2"></i>Corregir y Procesar XML';
    }
}

function renderSteps(steps) {
    document.getElementById('steps-list').innerHTML = steps.map(s => `
        <div class="step-row">
            <div class="step-dot ${s.ok ? 'bg-success bg-opacity-10' : 'bg-danger bg-opacity-10'}">
                <i class="bi ${s.ok ? 'bi-check-lg text-success' : 'bi-x-lg text-danger'}"></i>
            </div>
            <div>
                <div class="fw-semibold" style="font-size:13px">${esc(s.label)}</div>
                <div class="text-muted" style="font-size:12px">${esc(s.detail)}</div>
            </div>
        </div>
    `).join('');
}

/* ─── Render Results ─── */
function renderResults(d, docId, xml) {
    const inv = d.invoice;
    const sup = d.supplier;
    const cus = d.customer;
    const tot = d.totals;
    const cur = inv.currency;
    const fmt = v => parseFloat(v || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Header
    setText('d-inv-id', inv.id);
    setText('d-type-badge', inv.type_code === '01' ? 'FACTURA' : inv.type_code);
    setText('d-currency', cur);
    setText('d-order-ref-badge', 'O/S ' + inv.order_ref);
    setText('d-issue-date', inv.issue_date);
    setText('d-issue-time', inv.issue_time);
    setText('d-due-date', inv.due_date);
    setText('d-order-ref', inv.order_ref);

    // Totals
    setText('d-subtotal', cur + ' ' + fmt(tot.subtotal));
    setText('d-tax',      cur + ' ' + fmt(tot.tax));
    setText('d-total',    cur + ' ' + fmt(tot.total));
    setText('d-amount-words', d.amount_words);

    // Supplier
    setText('d-sup-name',  sup.name);
    setText('d-sup-ruc',   sup.ruc);
    setText('d-sup-addr',  sup.address + ', ' + sup.district + ' - ' + sup.city);
    setText('d-sup-email', sup.email);
    setText('d-sup-phone', sup.phone);

    // Customer
    setText('d-cus-name', cus.name);
    setText('d-cus-ruc',  cus.ruc);
    setText('d-cus-addr', cus.address + ', ' + cus.district + ' - ' + cus.city);

    // Lines
    document.getElementById('d-lines').innerHTML = (d.lines || []).map(l => `
        <tr>
            <td class="text-muted">${esc(l.id)}</td>
            <td>
                <div class="fw-semibold" style="font-size:13px">${esc(l.description)}</div>
                <small class="text-muted">${esc(l.quantity)} ${esc(l.unit)}</small>
            </td>
            <td><code class="small">${esc(l.code)}</code></td>
            <td class="text-end">${fmt(l.price)}</td>
            <td class="text-end">${fmt(l.price_w_tax)}</td>
            <td class="text-end text-danger">${fmt(l.tax)}</td>
            <td class="text-end fw-semibold">${fmt(l.subtotal)}</td>
        </tr>
    `).join('');

    // Payments
    document.getElementById('d-payments').innerHTML = (d.payments || []).map(p => `
        <tr>
            <td>${esc(p.means)}</td>
            <td class="text-end fw-semibold">${cur} ${fmt(p.amount)}</td>
            <td class="text-muted">${p.due_date || '—'}</td>
        </tr>
    `).join('');

    // Logistics
    const adj = d.adjuntos;
    const logFields = [
        ['Ref. Cliente',  adj.ref_cliente],
        ['Póliza',        adj.poliza],
        ['Fecha Llegada', adj.fecha_llegada],
        ['Nave',          adj.nave],
        ['Bultos',        adj.bultos],
        ['Peso Bruto',    adj.peso_bruto ? adj.peso_bruto + ' kg' : ''],
        ['Régimen',       adj.regimen],
        ['Mercadería',    adj.mercaderia],
        ['FOB',           adj.fob ? 'USD ' + fmt(adj.fob) : ''],
        ['Seguro',        adj.seguro ? 'USD ' + fmt(adj.seguro) : ''],
        ['Flete',         adj.flete ? 'USD ' + fmt(adj.flete) : ''],
        ['CIF USD',       adj.cif_dolar ? 'USD ' + fmt(adj.cif_dolar) : ''],
        ['CIF Soles',     adj.cif_soles ? 'S/ ' + fmt(adj.cif_soles) : ''],
        ['Tipo de Cambio',adj.tipo_cambio],
    ];
    document.getElementById('d-logistics').innerHTML = logFields.map(([k, v]) => `
        <div class="col-6 col-md-4 log-item">
            <div class="lk">${esc(k)}</div>
            <div class="lv">${esc(v) || '<span class="text-muted">—</span>'}</div>
        </div>
    `).join('');

    // Banks
    document.getElementById('d-banks').innerHTML = (d.bank_accounts || []).map(b => `
        <tr>
            <td style="font-size:12px">${esc(b.bank)}</td>
            <td>
                <span class="badge ${b.currency === 'DOLARES' ? 'bg-success' : 'bg-primary'} bg-opacity-10 ${b.currency === 'DOLARES' ? 'text-success' : 'text-primary'}" style="font-size:10px">
                    ${esc(b.currency)}
                </span>
            </td>
            <td class="font-monospace" style="font-size:11px">${esc(b.account)}</td>
            <td class="font-monospace text-muted" style="font-size:11px">${esc(b.cci)}</td>
        </tr>
    `).join('');

    // Leyendas
    setText('d-leyenda2', d.leyenda2);
    setText('d-leyenda3', d.leyenda3);

    // Emission form
    document.getElementById('f-datos').value  = xml;
    document.getElementById('f-docid').value  = docId;
    document.getElementById('xml-preview').textContent = xml.substring(0, 600) + (xml.length > 600 ? '\n[... ' + xml.length + ' chars total, clic para ver completo]' : '');
    document.getElementById('xml-modal-body').textContent = xml;
}

/* ─── XML Modal ─── */
function openXmlModal() { bsXmlModal.show(); }

/* ─── Preview Modal ─── */
function openPreview() {
    document.getElementById('pv-comando').textContent = document.getElementById('f-comando').value;
    document.getElementById('pv-docid').textContent   = document.getElementById('f-docid').value;
    // Reset to raw view
    setXmlView('raw', document.getElementById('view-btn-raw'));
    bsPreviewModal.show();
}

function setXmlView(mode, btn) {
    // Toggle active button
    ['view-btn-raw','view-btn-pretty','view-btn-tree'].forEach(id => {
        document.getElementById(id).classList.remove('active');
    });
    btn.classList.add('active');

    // Hide all views
    document.getElementById('pv-raw').style.display    = 'none';
    document.getElementById('pv-pretty').style.display = 'none';
    document.getElementById('pv-tree').style.display   = 'none';

    if (mode === 'raw') {
        const el = document.getElementById('pv-raw');
        el.textContent  = fixedXml;
        el.style.display = '';
    } else if (mode === 'pretty') {
        const el = document.getElementById('pv-pretty');
        el.textContent  = prettifyXml(fixedXml);
        el.style.display = '';
    } else if (mode === 'tree') {
        const el = document.getElementById('pv-tree');
        el.innerHTML    = buildXmlTree(fixedXml);
        el.style.display = '';
    }
}

/* ─── XML Prettifier ─── */
function prettifyXml(xml) {
    const TAB = '  ';
    let result = '';
    let depth  = 0;
    // Split on tags while keeping them
    const parts = xml.replace(/>\s*</g, '><').split(/(<[^>]+>)/);
    parts.forEach(part => {
        if (!part.trim()) return;
        if (part.startsWith('</')) {
            depth = Math.max(0, depth - 1);
            result += TAB.repeat(depth) + part + '\n';
        } else if (part.startsWith('<') && !part.startsWith('<' + '?') && !part.endsWith('/>') && !part.startsWith('<!--')) {
            result += TAB.repeat(depth) + part + '\n';
            depth++;
        } else if (part.startsWith('<' + '?') || part.endsWith('/>')) {
            result += TAB.repeat(depth) + part + '\n';
        } else {
            // Text content — attach to previous line
            result = result.trimEnd() + part;
        }
    });
    return result.trim();
}

/* ─── XML Tree Builder ─── */
function buildXmlTree(xmlStr) {
    const parser = new DOMParser();
    const doc    = parser.parseFromString(xmlStr, 'text/xml');
    const err    = doc.querySelector('parsererror');
    if (err) return `<span style="color:#f38ba8">Error al parsear XML: ${esc(err.textContent)}</span>`;
    return renderXmlNode(doc.documentElement);
}

function renderXmlNode(node) {
    const tag = node.tagName;
    const text = Array.from(node.childNodes)
        .filter(n => n.nodeType === Node.TEXT_NODE)
        .map(n => n.textContent.trim()).join('').trim();
    const children = Array.from(node.children);

    if (children.length === 0) {
        if (!text) return `<div class="xml-leaf"><span class="xt-tag">&lt;${esc(tag)}&gt;</span><span class="xt-empty"> vacío</span><span class="xt-tag">&lt;/${esc(tag)}&gt;</span></div>`;
        return `<div class="xml-leaf"><span class="xt-tag">&lt;${esc(tag)}&gt;</span><span class="xt-text">${esc(text)}</span><span class="xt-tag">&lt;/${esc(tag)}&gt;</span></div>`;
    }

    const uid = 'x' + Math.random().toString(36).slice(2, 9);
    const inner = children.map(renderXmlNode).join('');
    return `<div>
        <div class="xml-toggle" onclick="toggleXmlNode('${uid}',this)">
            <span class="toggle-icon" id="ti-${uid}">▼</span>
            <span class="xt-tag">&lt;${esc(tag)}&gt;</span>
            <span style="color:#6e738d;font-size:10px">${children.length} nodo${children.length>1?'s':''}</span>
        </div>
        <div class="xml-children" id="${uid}">${inner}</div>
        <div style="margin-left:18px"><span class="xt-tag">&lt;/${esc(tag)}&gt;</span></div>
    </div>`;
}

function toggleXmlNode(uid, el) {
    const children = document.getElementById(uid);
    const icon     = document.getElementById('ti-' + uid);
    const collapsed = children.style.display === 'none';
    children.style.display = collapsed ? '' : 'none';
    icon.classList.toggle('closed', !collapsed);
}

/* ─── Download (ISO-8859-1 real via backend) ─── */
function downloadXml() {
    const docId = document.getElementById('f-docid').value || 'comprobante';
    const form  = document.createElement('form');
    form.method = 'POST';
    form.action = '/download';

    const token = document.createElement('input');
    token.type  = 'hidden';
    token.name  = '_token';
    token.value = csrfToken();

    const datos = document.createElement('input');
    datos.type  = 'hidden';
    datos.name  = 'datos';
    datos.value = fixedXml;

    const docid = document.createElement('input');
    docid.type  = 'hidden';
    docid.name  = 'docid';
    docid.value = docId;

    form.appendChild(token);
    form.appendChild(datos);
    form.appendChild(docid);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function copyXmlFixed(btn) {
    navigator.clipboard.writeText(fixedXml).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check me-1"></i>Copiado!';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}

function copyField(id, btn) {
    const txt = document.getElementById(id).textContent;
    navigator.clipboard.writeText(txt).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check me-1"></i>Copiado!';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}

function copyXml(btn) {
    navigator.clipboard.writeText(fixedXml).then(() => {
        btn.innerHTML = '<i class="bi bi-check me-1"></i>Copiado!';
        setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copiar XML', 2000);
    });
}

/* ─── Emit ─── */
async function emitir() {
    const btn    = document.getElementById('emit-btn');
    const pWrap  = document.getElementById('emit-progress-wrap');
    const rBox   = document.getElementById('resp-box');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Emitiendo...';
    pWrap.style.display = '';
    rBox.style.display  = 'none';
    rBox.className      = 'resp-box mt-3';

    let emitted = false;
    setEmitProgress(0, 'Conectando con servidor...');
    setBar('emit-pbar', 0, 0); setBar('emit-pbar', 60, 1.5);
    document.getElementById('emit-pct-txt').textContent = '0%';

    const payload = {
        datos:   document.getElementById('f-datos').value,
        comando: document.getElementById('f-comando').value,
        docid:   document.getElementById('f-docid').value,
    };

    function isoFormEncode(str) {
        let out = '';
        for (let i = 0; i < str.length; i++) {
            const c = str.charCodeAt(i);
            if (c === 32) { out += '+'; }
            else if ((c >= 48 && c <= 57) || (c >= 65 && c <= 90) || (c >= 97 && c <= 122)
                     || c === 45 || c === 95 || c === 46 || c === 126) {
                out += str[i];
            } else if (c <= 255) {
                out += '%' + c.toString(16).toUpperCase().padStart(2, '0');
            } else {
                out += '%3F';
            }
        }
        return out;
    }

    function parseCa4Response(text) {
        const clean = text.replace(/\r?\n/g, '');
        const parts = clean.split('|');
        return {
            status: parts[0] || '',
            tipo:   parts[2] || '',
            serie:  parts[3] || '',
            numero: parts[4] || '',
            igv:    parts[5] || '',
            total:  parts[6] || '',
            fecha:  parts[7] || '',
            hash:   parts[10] || '',
            firma:  parts[11] || '',
            url:    parts[12] || '',
            pdf:    parts[13] || '',
        };
    }

    async function saveEmission(parsed, xmlEnviado, rawResp, comando) {
        try {
            await fetch('/save-emission', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({
                    doc_id:        parsed.serie + '-' + parsed.numero,
                    serie:         parsed.serie,
                    numero:        parsed.numero,
                    tipo:          parsed.tipo,
                    status:        parsed.status,
                    igv:           parsed.igv,
                    total:         parsed.total,
                    fecha:         parsed.fecha,
                    url_acepta:    parsed.url,
                    hash:          parsed.hash,
                    firma:         parsed.firma,
                    xml_enviado:   xmlEnviado,
                    respuesta_raw: rawResp,
                    comando:       comando,
                }),
            });
        } catch (_) {}
    }

    try {
        setEmitProgress(30, 'Enviando XML...');

        let text = null;
        let viaServer = false;

        // 1st attempt: server-side /submit (works when server is on same LAN as ca4xml)
        console.log('[ca4xml] intentando vía servidor /submit...');
        try {
            const rs = await fetch('/submit', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body:    JSON.stringify(payload),
            });
            const rj = await rs.json();
            if (rj.ok && rj.body) {
                text = rj.body;
                viaServer = true;
                console.log('[ca4xml] respuesta vía servidor OK:', text);
            } else if (rj.error) {
                throw new Error(rj.error);
            }
        } catch (serverErr) {
            console.warn('[ca4xml] servidor no alcanzó ca4xml:', serverErr.message, '— intentando browser-direct');
        }

        // 2nd attempt: browser-direct CORS
        if (!viaServer) {
            const formBody = 'datos=' + isoFormEncode(payload.datos)
                           + '&comando=' + isoFormEncode(payload.comando)
                           + '&docid='   + isoFormEncode(payload.docid);
            try {
                const r1 = await fetch('http://192.168.1.149:5002/ca4xml', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body:    formBody,
                });
                text = await r1.text();
                console.log('[ca4xml] respuesta browser-direct OK:', text);
            } catch (corsErr) {
                // 3rd attempt: no-cors (data sends, response unreadable)
                console.warn('[ca4xml] CORS bloqueó — enviando no-cors:', corsErr.message);
                await fetch('http://192.168.1.149:5002/ca4xml', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body:    formBody,
                    mode:    'no-cors',
                });
                console.log('[ca4xml] no-cors enviado');
                // No response available — show sent confirmation
                await countdownWait(20);
                setBar('emit-pbar', 100, 0.3);
                await sleep(400);
                document.getElementById('emit-pct-txt').textContent = '100%';
                setEmitProgress(100, 'Enviado a ca4xml');
                document.querySelector('#emit-modal .modal-title').innerHTML =
                    '<i class="bi bi-send-check-fill text-warning me-2"></i>XML Enviado a ca4xml';
                document.getElementById('er-docid').textContent = payload.docid;
                document.getElementById('er-hash').textContent  = 'Sin respuesta (CORS bloqueado)';
                document.getElementById('er-firma').textContent = '';
                document.getElementById('er-raw').textContent   = 'Enviado vía no-cors. Verificar en sistema receptor ca4xml.';
                document.getElementById('er-url').href = '#';
                document.getElementById('er-url-txt').textContent = 'Verificar en ca4xml';
                emitted = true;
                bsEmitModal.show();
                saveEmission({ status:'SENT', serie: payload.docid.split('-')[0]||'', numero: payload.docid.split('-')[1]||'',
                               tipo:'', igv:'', total:'', fecha:'', hash:'', firma:'', url:'' },
                             payload.datos, '(no-cors — sin respuesta)', payload.comando);
                return;
            }
        }

        await countdownWait(20);
        setBar('emit-pbar', 100, 0.3);
        await sleep(400);
        document.getElementById('emit-pct-txt').textContent = '100%';

        const parsed = parseCa4Response(text);
        if (parsed.status === 'OK') {
            setEmitProgress(100, 'Emitido correctamente');
            document.querySelector('#emit-modal .modal-title').innerHTML =
                '<i class="bi bi-check-circle-fill text-success me-2"></i>Emisión Exitosa';
            document.getElementById('er-docid').textContent = parsed.serie + '-' + parsed.numero;
            document.getElementById('er-hash').textContent  = parsed.hash;
            document.getElementById('er-firma').textContent = parsed.firma;
            document.getElementById('er-raw').textContent   = text;
            const urlEl  = document.getElementById('er-url');
            const urlTxt = document.getElementById('er-url-txt');
            if (parsed.url && parsed.url.startsWith('http')) {
                urlEl.href = parsed.url;
                urlTxt.textContent = parsed.url;
            } else {
                urlEl.href = '#';
                urlTxt.textContent = 'No disponible';
            }
            emitted = true;
            bsEmitModal.show();
            saveEmission(parsed, payload.datos, text, payload.comando);
        } else {
            rBox.style.display = '';
            rBox.classList.add('err');
            rBox.textContent = 'ERROR ca4xml:\n' + text;
            setEmitProgress(100, 'Error en la emisión');
            saveEmission(parsed, payload.datos, text, payload.comando);
        }

    } catch (e) {
        rBox.style.display = '';
        rBox.classList.add('err');
        rBox.textContent = 'Error de red: ' + e.message;
        setBar('emit-pbar', 100, 0.2);
        setEmitProgress(100, 'Error de conexión');
    } finally {
        if (!emitted) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-2"></i>Emitir Comprobante';
        } else {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-check-circle-fill me-2 text-success"></i>Comprobante Emitido';
        }
    }
}

function setEmitProgress(pct, txt) {
    document.getElementById('emit-status-txt').textContent = txt;
    document.getElementById('emit-pct-txt').textContent    = pct + '%';
}

async function countdownWait(seconds) {
    setBar('emit-pbar', 90, seconds);
    for (let i = seconds; i > 0; i--) {
        setEmitProgress(90, `Procesando en ca4xml... (${i}s)`);
        document.getElementById('emit-pct-txt').textContent = Math.round(60 + (seconds - i) / seconds * 30) + '%';
        await sleep(1000);
    }
}

/* ─── Reset ─── */
function resetApp() {
    clearFile();
    document.getElementById('xml-paste').value = '';
    fixedXml = '';
    hide('proc-status');
    hide('sec-results');
    document.getElementById('resp-box').style.display  = 'none';
    document.getElementById('emit-progress-wrap').style.display = 'none';
    setBar('proc-pbar', 0, 0);
    setBar('emit-pbar', 0, 0);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ─── Helpers ─── */
function csrfToken() { return document.querySelector('meta[name=csrf-token]').content; }
function show(id) { document.getElementById(id).style.display = ''; }
function hide(id) { document.getElementById(id).style.display = 'none'; }
function setText(id, txt) { document.getElementById(id).textContent = txt || ''; }
function esc(s) {
    if (s === null || s === undefined) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

function setBar(id, pct, secs = 0.5) {
    const bar = document.getElementById(id);
    if (!bar) return;
    bar.style.transition = pct === 0 ? 'none' : `width ${secs}s ease`;
    bar.style.width = pct + '%';
}

/* ─── Historial ─── */
let hData = [];
let hLoaded  = false;
let hOpen    = false;

function toggleHistorial() {
    const histCol   = document.getElementById('historial-col');
    const uploadCol = document.getElementById('upload-col');
    hOpen = !hOpen;
    if (hOpen) {
        uploadCol.style.maxWidth = '440px';
        histCol.style.maxWidth   = '900px';
        loadHistorial();
    } else {
        uploadCol.style.maxWidth = '1400px';
        histCol.style.maxWidth   = '0';
    }
}

async function loadHistorial(force = false) {
    if (hLoaded && !force) return;
    try {
        const r    = await fetch('/api/emissions');
        hData      = await r.json();
        hLoaded    = true;
        renderHistorial(hData);
    } catch (e) {
        document.getElementById('h-tbody').innerHTML =
            `<tr><td colspan="7" style="text-align:center;padding:2rem;color:#f38ba8;">Error al cargar: ${e.message}</td></tr>`;
    }
}

function renderHistorial(rows) {
    const ok = rows.filter(r => r.status === 'OK').length;
    document.getElementById('h-stat-total').textContent = rows.length;
    document.getElementById('h-stat-ok').textContent    = ok;

    const tbody = document.getElementById('h-tbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:#6e7681;">Sin emisiones</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(r => {
        const badgeCls = r.status === 'OK' ? 'bg-success' : r.status === 'SENT' ? 'bg-primary' : 'bg-danger';
        const cdr = r.url_acepta && r.url_acepta.startsWith('http')
            ? `<a href="${esc(r.url_acepta)}" target="_blank" class="btn btn-outline-success btn-sm py-0 px-1" style="font-size:11px;"><i class="bi bi-box-arrow-up-right"></i></a>`
            : '<span class="text-muted">—</span>';
        return `<tr>
            <td class="text-muted">${r.id}</td>
            <td class="text-muted">${r.created_at ? r.created_at.substring(0,16).replace('T',' ') : '—'}</td>
            <td class="fw-semibold">${esc(r.serie)}-${esc(r.numero)}</td>
            <td class="text-muted">${r.tipo ? 'T'+esc(r.tipo) : '—'}</td>
            <td><span class="badge ${badgeCls}" style="font-size:10px;">${esc(r.status)}</span></td>
            <td>${cdr}</td>
            <td><button onclick="showHDetail(${r.id})" class="btn btn-outline-secondary btn-sm py-0 px-1" style="font-size:11px;"><i class="bi bi-eye"></i></button></td>
        </tr>`;
    }).join('');
}

function filterHistorial() {
    const q = document.getElementById('h-search').value.toLowerCase();
    const filtered = hData.filter(r =>
        (r.serie+'-'+r.numero).toLowerCase().includes(q) ||
        (r.status||'').toLowerCase().includes(q) ||
        (r.created_at||'').toLowerCase().includes(q)
    );
    renderHistorial(filtered);
}

function showHDetail(id) {
    // Fetch full record including xml/response
    fetch('/api/emissions/' + id)
        .then(r => r.json())
        .then(e => {
            document.getElementById('d-id').textContent   = id;
            document.getElementById('d-resp').textContent = e.respuesta_raw || '(sin respuesta)';
            document.getElementById('d-xml').textContent  = e.xml_enviado  || '(sin XML)';
            showTab('tab-resp');
            bsDetailModal.show();
        })
        .catch(() => alert('Error cargando detalle'));
}
</script>
</body>
</html>
