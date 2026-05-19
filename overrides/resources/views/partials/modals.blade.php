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
                <pre id="pv-raw" style="font-family:'Consolas','Courier New',monospace;font-size:12px;background:#0d1117;color:#79c0ff;padding:1rem;border-radius:8px;white-space:pre-wrap;word-break:break-all;max-height:420px;overflow-y:auto;border:1px solid #30363d;line-height:1.6;"></pre>
                <div id="pv-pretty" class="xml-pretty" style="display:none"></div>
                <div id="pv-tree"   class="xml-tree"   style="display:none"></div>
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
