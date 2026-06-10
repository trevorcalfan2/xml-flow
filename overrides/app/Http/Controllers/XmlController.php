<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Emission;

class XmlController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function testView()
    {
        return view('test');
    }

    public function testConnection(Request $request)
    {
        $target  = 'http://192.168.1.149:5002/ca4xml';
        $comando = $request->input('comando', 'emitir');
        $docid   = $request->input('docid', 'TEST-00000001');
        $datos   = $request->input('datos', '<?xml version="1.0"?><test/>');
        $log     = [];

        $log[] = '[' . now() . '] Iniciando test de conexión';
        $log[] = "Target: $target";
        $log[] = "Comando: $comando | Docid: $docid";

        try {
            $log[] = 'Enviando POST...';
            $response = Http::timeout(10)
                ->asForm()
                ->post($target, [
                    'datos'   => mb_convert_encoding($datos, 'ISO-8859-1', 'UTF-8'),
                    'comando' => $comando,
                    'docid'   => $docid,
                ]);
            $log[] = 'HTTP Status: ' . $response->status();
            $log[] = 'Response body: ' . $response->body();
            return response()->json([
                'ok'      => true,
                'status'  => $response->status(),
                'body'    => $response->body(),
                'log'     => $log,
            ]);
        } catch (\Exception $e) {
            $log[] = 'EXCEPTION: ' . $e->getMessage();
            $log[] = 'Class: ' . get_class($e);
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'log'   => $log,
            ], 502);
        }
    }

    public function process(Request $request)
    {
        $raw = null;

        if ($request->hasFile('xml_file')) {
            $raw = file_get_contents($request->file('xml_file')->getRealPath());
        } elseif ($request->filled('xml_content')) {
            $raw = $request->input('xml_content');
        }

        if (!$raw) {
            return response()->json(['error' => 'No XML provided.'], 422);
        }

        $steps = [];

        // Bytes are UTF-8 — scrub any invalid byte sequences (e.g. stray 0x83)
        $utf8 = mb_scrub($raw, 'UTF-8');
        $steps[] = ['label' => 'Encoding', 'detail' => 'UTF-8 (bytes saneados)', 'ok' => true];

        // Escape bare & in UTF-8 space
        $bareCount = preg_match_all('/&(?!(amp|lt|gt|quot|apos|#\d+|#x[0-9a-fA-F]+);)/', $utf8);
        $utf8 = preg_replace('/&(?!(amp|lt|gt|quot|apos|#\d+|#x[0-9a-fA-F]+);)/', '&amp;', $utf8);

        $steps[] = [
            'label'  => 'Escape de & sin escapar',
            'detail' => $bareCount > 0 ? "{$bareCount} reemplazado(s)" : 'Ninguno encontrado',
            'ok'     => true,
        ];

        // Fix common typo in amount-in-words
        $typoCount = substr_count($utf8, 'Doláres') + substr_count($utf8, 'doláres');
        $utf8 = str_replace(['Doláres', 'doláres'], ['Dólares', 'dólares'], $utf8);
        $steps[] = [
            'label'  => 'Corrección tipográfica',
            'detail' => $typoCount > 0 ? "{$typoCount} — \"Doláres\" → \"Dólares\"" : 'Sin errores tipográficos',
            'ok'     => true,
        ];

        // Force ISO-8859-1 in XML declaration
        $fixedUtf8 = preg_replace('/encoding=["\'][^"\']+["\']/', 'encoding="ISO-8859-1"', $utf8, 1);
        $steps[] = ['label' => 'Declaración', 'detail' => 'encoding="ISO-8859-1" preservado', 'ok' => true];

        // Strip XML declaration entirely — simplexml defaults to UTF-8, reads Ñ/tildes correctly
        $utf8ForParsing = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $utf8, 1);

        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($utf8ForParsing);
        $xmlErrors = libxml_get_errors();
        libxml_clear_errors();

        if ($doc === false) {
            $errMsg = !empty($xmlErrors) ? trim($xmlErrors[0]->message) : 'XML inválido';
            $steps[] = ['label' => 'Validación XML', 'detail' => $errMsg, 'ok' => false];
            return response()->json(['steps' => $steps, 'error' => $errMsg]);
        }

        $steps[] = ['label' => 'Validación XML', 'detail' => 'Válido', 'ok' => true];

        $data  = $this->extractData($doc);
        $docId = (string) $doc->ID;

        // fixed_xml sent as UTF-8 for JSON transport; submit() converts to ISO-8859-1 before sending
        return response()->json([
            'steps'     => $steps,
            'fixed_xml' => $fixedUtf8,
            'data'      => $data,
            'doc_id'    => $docId,
        ]);
    }

    public function download(Request $request)
    {
        $datosUtf8 = $request->input('datos');
        $docId     = $request->input('docid', 'comprobante');

        $iso = mb_convert_encoding($datosUtf8, 'ISO-8859-1', 'UTF-8');

        return response($iso, 200, [
            'Content-Type'        => 'application/xml; charset=ISO-8859-1',
            'Content-Disposition' => 'attachment; filename="' . $docId . '-fixed.xml"',
        ]);
    }

    public function submit(Request $request)
    {
        $datosUtf8 = $request->input('datos');
        $comando   = $request->input('comando', 'emitir');
        $docid     = $request->input('docid');

        // Convert XML back to ISO-8859-1 for the receptor
        $datos = mb_convert_encoding($datosUtf8, 'ISO-8859-1', 'UTF-8');

        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post('http://192.168.1.149:5002/ca4xml', [
                    'datos'   => $datos,
                    'comando' => $comando,
                    'docid'   => $docid,
                ]);

            return response()->json([
                'http_code' => $response->status(),
                'body'      => $response->body(),
                'ok'        => $response->successful(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'ok' => false], 502);
        }
    }

    public function saveEmission(Request $request)
    {
        try {
            $emission = Emission::create([
                'doc_id'       => $request->input('doc_id', ''),
                'serie'        => $request->input('serie', ''),
                'numero'       => $request->input('numero', ''),
                'tipo'         => $request->input('tipo', ''),
                'status'       => $request->input('status', 'OK'),
                'igv'          => $request->input('igv') ?: null,
                'total'        => $request->input('total') ?: null,
                'fecha_emision'=> $request->input('fecha') ?: null,
                'url_acepta'   => $request->input('url_acepta', ''),
                'hash'         => $request->input('hash', ''),
                'firma'        => $request->input('firma', ''),
                'xml_enviado'  => $request->input('xml_enviado', ''),
                'respuesta_raw'=> $request->input('respuesta_raw', ''),
                'comando'      => $request->input('comando', 'emitir'),
            ]);
            return response()->json(['ok' => true, 'id' => $emission->id]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public function historial()
    {
        $emissions = Emission::orderByDesc('created_at')->get();
        return view('historial', compact('emissions'));
    }

    public function emissionsApi()
    {
        $emissions = Emission::orderByDesc('created_at')
            ->select('id','doc_id','serie','numero','tipo','status','igv','total','fecha_emision','url_acepta','comando','created_at')
            ->get();
        return response()->json($emissions);
    }

    public function emissionDetail($id)
    {
        $e = Emission::findOrFail($id);
        return response()->json($e);
    }

    private function extractData(\SimpleXMLElement $doc): array
    {
        $supplier = $doc->AccountingSupplierParty;
        $customer = $doc->AccountingCustomerParty;
        $monetary = $doc->LegalMonetaryTotal;
        $adj      = $doc->Adjuntos;

        $lines = [];
        foreach ($doc->InvoiceLine as $line) {
            $lines[] = [
                'id'          => (string) $line->ID,
                'quantity'    => (string) $line->InvoicedQuantity,
                'unit'        => (string) $line->UnitCode,
                'description' => (string) $line->Item->Description,
                'code'        => (string) $line->Item->SellersItemIdentification->ID,
                'price'       => (float)  (string) $line->Price->PriceAmount,
                'price_w_tax' => (float)  (string) $line->PricingReference->AlternativeConditionPrice->PriceAmount,
                'subtotal'    => (float)  (string) $line->LineExtensionAmount,
                'tax'         => (float)  (string) $line->TaxAmount,
            ];
        }

        $payments = [];
        foreach ($doc->PaymentForm as $pf) {
            $payments[] = [
                'means'    => (string) $pf->PaymentMeansID,
                'amount'   => (float)  (string) $pf->Amount,
                'due_date' => (string) $pf->PaymentDueDate,
            ];
        }

        $banks   = explode('^', (string) $adj->Banco);
        $monedas = explode('^', (string) $adj->Moneda);
        $cuentas = explode('^', (string) $adj->Cuenta);
        $ccis    = explode('^', (string) $adj->CCI);

        $bankAccounts = [];
        foreach ($banks as $i => $bank) {
            if (trim($bank) === '') continue;
            $bankAccounts[] = [
                'bank'     => trim($bank),
                'currency' => trim($monedas[$i] ?? ''),
                'account'  => trim($cuentas[$i] ?? ''),
                'cci'      => trim($ccis[$i] ?? ''),
            ];
        }

        return [
            'invoice' => [
                'id'         => (string) $doc->ID,
                'issue_date' => (string) $doc->IssueDate,
                'issue_time' => (string) $doc->IssueTime,
                'due_date'   => (string) $doc->DueDate,
                'currency'   => (string) $doc->DocumentCurrencyCode,
                'order_ref'  => (string) $doc->OrderReference->ID,
                'type_code'  => (string) $doc->InvoiceTypeCode,
            ],
            'supplier' => [
                'ruc'     => (string) $supplier->CustomerAssignedAccountID,
                'name'    => (string) $supplier->Party->PartyName->Name,
                'address' => (string) $supplier->Party->PostalAddress->RegistrationAddress->StreetName,
                'city'    => (string) $supplier->Party->PostalAddress->RegistrationAddress->CityName,
                'district'=> (string) $supplier->Party->PostalAddress->RegistrationAddress->District,
                'email'   => (string) $supplier->Party->ElectronicMail,
                'phone'   => (string) $supplier->Party->Telephone,
                'web'     => (string) $supplier->Party->WebsiteURI,
            ],
            'customer' => [
                'ruc'     => (string) $customer->CustomerAssignedAccountID,
                'name'    => (string) $customer->Party->PartyLegalEntity->RegistrationName,
                'address' => (string) $customer->Party->PostalAddress->RegistrationAddress->StreetName,
                'city'    => (string) $customer->Party->PostalAddress->RegistrationAddress->CityName,
                'district'=> (string) $customer->Party->PostalAddress->RegistrationAddress->District,
            ],
            'totals' => [
                'subtotal'  => (float) (string) $monetary->LineExtensionAmount,
                'tax'       => (float) (string) $monetary->TaxAmount,
                'total'     => (float) (string) $monetary->PayableAmount,
                'allowance' => (float) (string) $monetary->AllowanceTotalAmount,
            ],
            'lines'        => $lines,
            'payments'     => $payments,
            'bank_accounts'=> $bankAccounts,
            'adjuntos' => [
                'ref_cliente'   => (string) $adj->RefCliente,
                'tipo_cambio'   => (string) $adj->TipoCambio,
                'poliza'        => (string) $adj->Poliza,
                'fecha_llegada' => (string) $adj->FechaLlegada,
                'nave'          => (string) $adj->Nave,
                'bultos'        => (string) $adj->Bultos,
                'peso_bruto'    => (string) $adj->PesoBruto,
                'regimen'       => (string) $adj->Regimen,
                'mercaderia'    => (string) $adj->Mercaderia,
                'fob'           => (string) $adj->FOB,
                'seguro'        => (string) $adj->Seguro,
                'flete'         => (string) $adj->Flete,
                'cif_dolar'     => (string) $adj->CIFDolar,
                'cif_soles'     => (string) $adj->CIFSoles,
                'observacion'   => (string) $adj->Observacion,
            ],
            'amount_words' => (string) $doc->AdditionalInformation->AdditionalProperty->Value,
            'leyenda2'     => (string) $adj->Leyenda2,
            'leyenda3'     => (string) $adj->Leyenda3,
        ];
    }
}
