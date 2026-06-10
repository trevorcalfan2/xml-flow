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

    // FIX: reset emit button on new process
    const emitBtn = document.getElementById('emit-btn');
    emitBtn.disabled = false;
    emitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Emitir Comprobante';

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
        // FIX: hide proc-status before showing results
        hide('proc-status');
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

    setText('d-inv-id', inv.id);
    setText('d-type-badge', inv.type_code === '01' ? 'FACTURA' : inv.type_code);
    setText('d-currency', cur);
    setText('d-order-ref-badge', 'O/S ' + inv.order_ref);
    setText('d-issue-date', inv.issue_date);
    setText('d-issue-time', inv.issue_time);
    setText('d-due-date', inv.due_date);
    setText('d-order-ref', inv.order_ref);

    setText('d-subtotal', cur + ' ' + fmt(tot.subtotal));
    setText('d-tax',      cur + ' ' + fmt(tot.tax));
    setText('d-total',    cur + ' ' + fmt(tot.total));
    setText('d-amount-words', d.amount_words);

    setText('d-sup-name',  sup.name);
    setText('d-sup-ruc',   sup.ruc);
    setText('d-sup-addr',  sup.address + ', ' + sup.district + ' - ' + sup.city);
    setText('d-sup-email', sup.email);
    setText('d-sup-phone', sup.phone);

    setText('d-cus-name', cus.name);
    setText('d-cus-ruc',  cus.ruc);
    setText('d-cus-addr', cus.address + ', ' + cus.district + ' - ' + cus.city);

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

    document.getElementById('d-payments').innerHTML = (d.payments || []).map(p => `
        <tr>
            <td>${esc(p.means)}</td>
            <td class="text-end fw-semibold">${cur} ${fmt(p.amount)}</td>
            <td class="text-muted">${p.due_date || '—'}</td>
        </tr>
    `).join('');

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

    setText('d-leyenda2', d.leyenda2);
    setText('d-leyenda3', d.leyenda3);

    document.getElementById('f-datos').value  = xml;
    document.getElementById('f-docid').value  = docId;
    document.getElementById('xml-preview').textContent = xml.substring(0, 600) + (xml.length > 600 ? '\n[... ' + xml.length + ' chars total, clic para ver completo]' : '');
    document.getElementById('xml-modal-body').textContent = xml;

    checkDuplicateEmission(docId);
}

async function checkDuplicateEmission(docId) {
    const btn  = document.getElementById('emit-btn');
    const rBox = document.getElementById('resp-box');
    try {
        const r    = await fetch('/api/emissions');
        const list = await r.json();
        const prev = list.find(e => e.status === 'OK' && (e.serie + '-' + e.numero) === docId);
        if (prev) {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Ya emitido — ver historial';
            rBox.style.display = '';
            rBox.className = 'resp-box mt-3 err';
            rBox.textContent = 'ADVERTENCIA: Este comprobante (' + docId + ') ya fue emitido exitosamente el ' + (prev.created_at || '').substring(0, 16).replace('T', ' ') + '. No se puede volver a emitir.';
        }
    } catch (_) {}
}

/* ─── XML Modal ─── */
function openXmlModal() { bsXmlModal.show(); }

/* ─── Preview Modal ─── */
function openPreview() {
    document.getElementById('pv-comando').textContent = document.getElementById('f-comando').value;
    document.getElementById('pv-docid').textContent   = document.getElementById('f-docid').value;
    setXmlView('raw', document.getElementById('view-btn-raw'));
    bsPreviewModal.show();
}

function setXmlView(mode, btn) {
    ['view-btn-raw','view-btn-pretty','view-btn-tree'].forEach(id => {
        document.getElementById(id).classList.remove('active');
    });
    btn.classList.add('active');
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

/* ─── Download ─── */
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

async function refreshCsrfToken() {
    try {
        const r = await fetch('/csrf-token');
        const j = await r.json();
        document.querySelector('meta[name=csrf-token]').setAttribute('content', j.token);
    } catch (_) {}
}

/* ─── Emit ─── */
async function emitir() {
    await refreshCsrfToken();

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
            status: parts[0]  || '',
            tipo:   parts[2]  || '',
            serie:  parts[3]  || '',
            numero: parts[4]  || '',
            igv:    parts[5]  || '',
            total:  parts[6]  || '',
            fecha:  parts[7]  || '',
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
            // FIX: force historial reload after emission
            hLoaded = false;
            if (hOpen) loadHistorial(true);
        } catch (_) {}
    }

    try {
        setEmitProgress(30, 'Enviando XML...');

        let text      = null;
        let viaServer = false;

        // 1st: server-side /submit
        console.log('[ca4xml] intentando vía servidor /submit...');
        try {
            const rs = await fetch('/submit', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body:    JSON.stringify(payload),
            });
            const rj = await rs.json();
            // FIX: check rj.ok alone, not rj.ok && rj.body
            if (rj.ok) {
                text = rj.body || '';
                viaServer = true;
                console.log('[ca4xml] respuesta vía servidor OK:', text);
            } else if (rj.error) {
                throw new Error(rj.error);
            }
        } catch (serverErr) {
            console.warn('[ca4xml] servidor no alcanzó ca4xml:', serverErr.message, '— intentando browser-direct');
        }

        // 2nd: browser-direct CORS
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
                // 3rd: no-cors fallback
                console.warn('[ca4xml] CORS bloqueó — enviando no-cors:', corsErr.message);
                await fetch('http://192.168.1.149:5002/ca4xml', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body:    formBody,
                    mode:    'no-cors',
                });
                console.log('[ca4xml] no-cors enviado');
                await countdownWait(20);
                setBar('emit-pbar', 100, 0.3);
                await sleep(400);
                document.getElementById('emit-pct-txt').textContent = '100%';
                setEmitProgress(100, 'Enviado a ca4xml');
                document.querySelector('#emit-modal .modal-title').innerHTML =
                    '<i class="bi bi-send-check-fill text-warning me-2"></i>XML Enviado a ca4xml';
                document.getElementById('er-docid').textContent = payload.docid;
                document.getElementById('er-fecha').textContent = '—';
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
            // FIX: populate er-fecha
            document.getElementById('er-fecha').textContent = parsed.fecha || '—';
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
    document.getElementById('resp-box').style.display         = 'none';
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
let hData   = [];
let hLoaded = false;
let hOpen   = false;

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
        const r = await fetch('/api/emissions');
        hData   = await r.json();
        hLoaded = true;
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
