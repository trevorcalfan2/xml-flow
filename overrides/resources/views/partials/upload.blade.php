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
</div>
