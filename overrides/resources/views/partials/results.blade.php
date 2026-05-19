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
                            <div class="xml-box" id="xml-preview" onclick="openXmlModal()" title="Ver XML completo"></div>
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

                        <!-- emit button -->
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
